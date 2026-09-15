<?php
require 'vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Locator\TableLocator;
use Cake\Database\Driver\Postgres;
use App\Model\Table\UsersTable;
use App\Model\Table\JobsTable;
use App\Policy\JobPolicy;

ConnectionManager::setConfig('default', [
    'className' => 'Cake\Database\Connection',
    'driver' => Postgres::class,
    'host' => 'localhost',
    'username' => 'postgres',
    'password' => 'root',
    'database' => 'embroidery_scheduler',
    'schema' => 'public',
    'port' => '5432',
]);

FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(true));

$usersTable = FactoryLocator::get('Table')->get('Users');
$jobsTable = FactoryLocator::get('Table')->get('Jobs');
$policy = new JobPolicy();

// Get users
$admin = $usersTable->find()->where(['email' => 'admin@stitchcraft.com'])->first();
$scheduler = $usersTable->find()->where(['email' => 'scheduler@stitchcraft.com'])->first();
$opDigitizing = $usersTable->find()->where(['email' => 'operator.digitizing@stitchcraft.com'])->first();
$opProgramming = $usersTable->find()->where(['email' => 'operator.programming@stitchcraft.com'])->first();
$qc = $usersTable->find()->where(['email' => 'qc@stitchcraft.com'])->first();
$production = $usersTable->find()->where(['email' => 'production@stitchcraft.com'])->first();

// Get jobs
$job1 = $jobsTable->find()->where(['job_number' => 'SCHED-001'])->first(); // created by scheduler, operator: digitizing
$job2 = $jobsTable->find()->where(['job_number' => 'SCHED-002'])->first(); // created by scheduler, operator: programming
$job3 = $jobsTable->find()->where(['job_number' => 'SCHED-003'])->first(); // created by scheduler, operator: data_entry

echo "=== canView Policy Tests ===\n\n";

echo "Job: {$job1->job_number} (created by scheduler, operator: digitizing)\n";
echo "  Admin: " . ($policy->canView($admin, $job1) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Production: " . ($policy->canView($production, $job1) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Scheduler (creator): " . ($policy->canView($scheduler, $job1) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Operator Digitizing (owner): " . ($policy->canView($opDigitizing, $job1) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Operator Programming (not owner): " . ($policy->canView($opProgramming, $job1) ? 'YES' : 'NO') . " (expected: NO)\n";
echo "  QC (assigned): " . ($policy->canView($qc, $job1) ? 'YES' : 'NO') . " (expected: YES)\n\n";

echo "Job: {$job2->job_number} (created by scheduler, operator: programming)\n";
echo "  Admin: " . ($policy->canView($admin, $job2) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Production: " . ($policy->canView($production, $job2) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Scheduler (creator): " . ($policy->canView($scheduler, $job2) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Operator Programming (owner): " . ($policy->canView($opProgramming, $job2) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Operator Digitizing (not owner): " . ($policy->canView($opDigitizing, $job2) ? 'YES' : 'NO') . " (expected: NO)\n";
echo "  QC (assigned): " . ($policy->canView($qc, $job2) ? 'YES' : 'NO') . " (expected: YES)\n\n";

echo "Job: {$job3->job_number} (created by scheduler, operator: data_entry)\n";
echo "  Admin: " . ($policy->canView($admin, $job3) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Production: " . ($policy->canView($production, $job3) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Scheduler (creator): " . ($policy->canView($scheduler, $job3) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  Operator Data Entry (owner): " . ($policy->canView($opProgramming, $job3) ? 'YES' : 'NO') . " (expected: NO - wait, this is wrong op)\n";
echo "  Operator Data Entry (owner): " . ($policy->canView($usersTable->find()->where(['email' => 'operator.dataentry@stitchcraft.com'])->first(), $job3) ? 'YES' : 'NO') . " (expected: YES)\n";
echo "  QC (assigned): " . ($policy->canView($qc, $job3) ? 'YES' : 'NO') . " (expected: YES)\n\n";

echo "=== All canView tests completed ===\n";