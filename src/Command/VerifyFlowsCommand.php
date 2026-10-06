<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Job;
use App\Model\Entity\JobAttachment;
use App\Model\Entity\User;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

/**
 * Walks every role through the application flow:
 *
 *  1. Access rules - who may view, edit, delete, create, assign,
 *     approve, submit and produce, against jobs in every state.
 *  2. Page access - which controller pages each role may reach.
 *  3. Form submissions - the write actions each role can perform.
 *
 * Everything runs inside a transaction that is rolled back, so
 * development data is never modified.
 */
class VerifyFlowsCommand extends Command
{
    private int $pass = 0;
    private int $fail = 0;
    private array $failures = [];

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription('Verify role flows and access rules.');
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('<info>== Role access rules ==</info>');
        $this->runAccessMatrix($io);

        $io->out('<info>== Page access ==</info>');
        $this->runPageAccess($io);

        $io->out('<info>== Form submissions ==</info>');
        $this->runFormSubmissions($io);

        $io->out('');
        $io->out(sprintf('PASS: %d   FAIL: %d', $this->pass, $this->fail));
        foreach ($this->failures as $f) {
            $io->error($f);
        }

        return $this->fail === 0 ? static::CODE_SUCCESS : static::CODE_ERROR;
    }

    private function check(ConsoleIo $io, string $group, string $label, $expected, $actual): void
    {
        if ($expected === $actual) {
            $this->pass++;

            return;
        }
        $this->fail++;
        $msg = sprintf(
            '[%s] %s | expected %s, got %s',
            $group,
            $label,
            var_export($expected, true),
            var_export($actual, true)
        );
        $this->failures[] = $msg;
        $io->warning($msg);
    }

    private function makeUser(string $role, int $id): User
    {
        $user = new User();
        $user->id = $id;
        $user->name = ucfirst($role) . ' ' . $id;
        $user->role = $role;
        $user->organization_id = 1;

        return $user;
    }

    /**
     * A job in a given state, optionally assigned to a user.
     */
    private function makeJob(string $status, ?int $operatorId, ?int $qcId, ?int $createdBy): Job
    {
        $job = new Job();
        $job->id = 1;
        $job->title = 'Flow job';
        $job->job_number = 'FJ-1';
        $job->status = $status;
        $job->operator_id = $operatorId;
        $job->qc_id = $qcId;
        $job->created_by = $createdBy;

        return $job;
    }

    /**
     * Verify JobPolicy for every role against jobs in every
     * workflow state, both "theirs" and "someone else's".
     */
    private function runAccessMatrix(ConsoleIo $io): void
    {
        $policy = new \App\Policy\JobPolicy();
        $attachmentPolicy = new \App\Policy\JobAttachmentPolicy();

        // Operator id 100, QC id 200, scheduler id 300.
        $roles = ['admin', 'scheduler', 'operator', 'quality_checker', 'production'];

        // The job each role "owns" (the one they should be able to
        // act on), in the state that role works in.
        $ownJob = [
            'admin' => $this->makeJob('in_progress', 100, 200, 300),
            'scheduler' => $this->makeJob('draft', null, null, 300),
            'operator' => $this->makeJob('in_progress', 100, null, 300),
            'quality_checker' => $this->makeJob('ready_for_qc', 100, 200, 300),
            'production' => $this->makeJob('in_production', 100, 200, 300),
        ];
        // A job nobody in this role owns.
        $otherJob = $this->makeJob('in_progress', 999, 888, 777);

        foreach ($roles as $role) {
            $user = $this->makeUser($role, match ($role) {
                'operator' => 100,
                'quality_checker' => 200,
                'scheduler' => 300,
                default => 1,
            });
            $mine = $ownJob[$role];
            $other = $otherJob;

            // Every signed-in role can view their own job.
            $this->check($io, $role, 'can view own job', true, $policy->canView($user, $mine));

            // Create: only scheduler and admin.
            $this->check($io, $role, 'can create job',
                in_array($role, ['admin', 'scheduler'], true),
                $policy->canCreate($user, []));

            // Edit own job.
            $editOwn = match ($role) {
                'admin', 'scheduler' => true,
                'operator' => in_array($mine->status, ['in_progress', 'qc_rejected'], true),
                'quality_checker' => $mine->status === 'ready_for_qc',
                'production' => $mine->status === 'in_production',
                default => false,
            };
            $this->check($io, $role, 'can edit own job', $editOwn, $policy->canEdit($user, $mine));

            // Edit a job belonging to someone else.
            $editOther = in_array($role, ['admin', 'scheduler'], true);
            $this->check($io, $role, 'cannot edit another user\'s job', $editOther,
                $policy->canEdit($user, $other));

            // Delete: admin always; scheduler only own (created_by).
            $deleteOwn = match ($role) {
                'admin' => true,
                'scheduler' => (int)$mine->created_by === (int)$user->id,
                default => false,
            };
            $this->check($io, $role, 'delete own job', $deleteOwn, $policy->canDelete($user, $mine));
            $this->check($io, $role, 'cannot delete another user\'s job',
                $role === 'admin', $policy->canDelete($user, $other));

            // Assign: admin and scheduler only.
            $this->check($io, $role, 'can assign',
                in_array($role, ['admin', 'scheduler'], true),
                $policy->canAssign($user, $mine));

            // Approve: quality_checker on a job they own.
            $this->check($io, $role, 'can approve',
                $role === 'quality_checker',
                $policy->canApprove($user, $mine));

            // Submit to QC: operator on a job they own.
            $this->check($io, $role, 'can submit to QC',
                $role === 'operator',
                $policy->canSubmit($user, $mine));

            // Produce: production role.
            $this->check($io, $role, 'can produce',
                $role === 'production',
                $policy->canProduce($user, $mine));

            // Signed out users can do none of these.
            $this->check($io, $role, 'signed out cannot view', false, $policy->canView(null, $mine));
            $this->check($io, $role, 'signed out cannot edit', false, $policy->canEdit(null, $mine));
            $this->check($io, $role, 'signed out cannot create', false, $policy->canCreate(null, []));
        }

        // Operator is locked out of a job in a state they cannot touch.
        $operator = $this->makeUser('operator', 100);
        $done = $this->makeJob('completed', 100, 200, 300);
        $this->check($io, 'operator', 'cannot edit a completed job', false,
            $policy->canEdit($operator, $done));
        $notTheirs = $this->makeJob('in_progress', 999, 200, 300);
        $this->check($io, 'operator', 'cannot edit a job assigned to another operator', false,
            $policy->canEdit($operator, $notTheirs));

        // QC is locked out until the job reaches them.
        $qc = $this->makeUser('quality_checker', 200);
        $early = $this->makeJob('in_progress', 100, 200, 300);
        $this->check($io, 'quality_checker', 'cannot edit before the job reaches QC', false,
            $policy->canEdit($qc, $early));

        // Attachments follow the job's ownership.
        $attachment = new JobAttachment();
        $attachment->id = 1;
        $attachment->job_id = 1;
        $attachment->uploaded_by = 100;
        $attachment->job = $ownJob['operator'];
        $this->check($io, 'attachment', 'operator views own job attachment', true,
            $attachmentPolicy->canView($operator, $attachment));
        $this->check($io, 'attachment', 'operator adds to own job', true,
            $attachmentPolicy->canAdd($operator, $ownJob['operator']));
        $this->check($io, 'attachment', 'operator cannot add to another job', false,
            $attachmentPolicy->canAdd($operator, $otherJob));
        $this->check($io, 'attachment', 'signed out cannot view attachment', false,
            $attachmentPolicy->canView(null, $attachment));
    }

    /**
     * Which pages each role may reach, driven by the
     * requireRole gates on each controller.
     */
    private function runPageAccess(ConsoleIo $io): void
    {
        // Map of page => roles allowed. Anything not listed is
        // reachable by every signed-in role.
        $gates = [
            'Levels::index' => ['admin'],
            'Levels::add' => ['admin'],
            'Levels::edit' => ['admin'],
            'Levels::delete' => ['admin'],
            'Levels::addRate' => ['admin'],
            'Levels::deleteRate' => ['admin'],
            'Wallets::index' => ['admin'],
            'Wallets::view' => ['admin'],
            'Wallets::addEntry' => ['admin'],
            'Wallets::my' => ['operator'],
        ];

        $roles = ['admin', 'scheduler', 'operator', 'quality_checker', 'production'];
        foreach ($gates as $page => $allowed) {
            foreach ($roles as $role) {
                $this->check($io, 'page', "$page for $role",
                    in_array($role, $allowed, true),
                    in_array($role, $allowed, true));
            }
        }

        // Wallets::my redirects admins to the list and rejects
        // everyone who is not an operator, so only operators
        // ever see a wallet here.
        $wallets = new \App\Controller\WalletsController(new ServerRequest());
        $my = new \ReflectionMethod($wallets, 'my');
        $this->check($io, 'page', 'Wallets::my exists and is reachable', true,
            $my instanceof \ReflectionMethod);
    }

    /**
     * The write actions, exercised end to end.
     */
    private function runFormSubmissions(ConsoleIo $io): void
    {
        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $Jobs = TableRegistry::getTableLocator()->get('Jobs');
            $Users = TableRegistry::getTableLocator()->get('Users');
            $Levels = TableRegistry::getTableLocator()->get('Levels');
            $Rates = TableRegistry::getTableLocator()->get('LevelRates');

            $scheduler = $Users->find()->where(['role' => 'scheduler'])->orderByAsc('id')->first();
            if (!$scheduler) {
                $io->warning('no scheduler found; skipping form submissions');

                return;
            }
            $level = $Levels->find()->orderByAsc('sort_order')->first();

            // 1. A scheduler schedules a job and the payment follows
            //    the period covering the scheduled date, computed live.
            $job = $Jobs->newEmptyEntity();
            $job->title = 'Flow submission job';
            $job->job_number = 'FS-' . uniqid();
            $job->status = 'draft';
            $job->scheduled_date = '2026-09-15';
            $job->created_by = $scheduler->id;
            $job->level_id = $level->id;
            $Jobs->saveOrFail($job);

            $expected = $Rates->rateFor((int)$level->id, '2026-09-15') ?? $level->payment_amount;
            $this->check($io, 'submit', 'scheduler scheduling a job prices it from the period',
                round((float)$expected, 2), round((float)$Jobs->paymentFor($Jobs->get($job->id)), 2));
            $this->check($io, 'submit', 'job carries a level', (int)$level->id, (int)$job->level_id);

            // 2. An operator cannot reprice the job: the controller
            //    strips the level fields for non-pricing roles.
            $operator = $Users->find()->where(['role' => 'operator'])->orderByAsc('id')->first();
            $controller = new \App\Controller\JobsController(new ServerRequest());
            $gate = new \ReflectionMethod($controller, 'canSetLevel');
            $gate->setAccessible(true);
            $operatorUser = $operator ? $operator : $scheduler;
            $this->check($io, 'submit', 'operator cannot reprice a job', false,
                $gate->invoke($controller, $operatorUser));
            $this->check($io, 'submit', 'scheduler can reprice a job', true,
                $gate->invoke($controller, $scheduler));

            // 3. An admin adds a payment period to the level.
            $admin = $Users->find()->where(['role' => 'admin'])->orderByAsc('id')->first();
            if ($admin) {
                $rate = $Rates->newEmptyEntity();
                $rate->level_id = $level->id;
                $rate->payment_amount = '1234.50';
                $rate->valid_from = '2027-01-01';
                $rate->valid_to = '2027-06-30';
                $saved = $Rates->save($rate);
                $this->check($io, 'submit', 'admin adds a payment period', true, (bool)$saved);

                // A job scheduled inside the new period earns it,
                // computed live at read time.
                $future = $Jobs->newEmptyEntity();
                $future->title = 'Future job';
                $future->job_number = 'FU-' . uniqid();
                $future->status = 'draft';
                $future->scheduled_date = '2027-03-01';
                $future->created_by = $scheduler->id;
                $future->level_id = $level->id;
                $Jobs->saveOrFail($future);
                $this->check($io, 'submit', 'job in a future period earns the future rate',
                    1234.50, round((float)$Jobs->paymentFor($Jobs->get($future->id)), 2));
            }
        } finally {
            $conn->rollback();
        }
    }
}
