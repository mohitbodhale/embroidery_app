<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $role
 * @var array $counts
 * @var int $myCount
 * @var iterable $recent
 */
$this->assign('title', 'Dashboard');

$roleLabel = ucwords(str_replace('_', ' ', $role));
$roleColor = [
    'admin' => 'danger', 'scheduler' => 'primary', 'operator' => 'info',
    'quality_checker' => 'warning', 'production' => 'success', 'pending' => 'secondary',
][$role] ?? 'secondary';

$statusLabel = function ($s) {
    return h(ucwords(str_replace('_', ' ', (string)$s)));
};
$statusColor = [
    'draft' => 'secondary', 'pending_approval' => 'warning', 'in_progress' => 'info',
    'ready_for_qc' => 'primary', 'qc_rejected' => 'danger', 'qc_approved' => 'success',
    'in_production' => 'info', 'completed' => 'success',
];
?>
<div class="row g-3">
    <div class="col-12">
        <div class="alert-card info">
            <div>
                <i class="fas fa-id-badge me-2"></i>
                Signed in as <strong><?= h($user->name) ?></strong>
                <span class="badge bg-<?= $roleColor ?> ms-1"><?= h($roleLabel) ?></span>
            </div>
            <span class="text-muted small"><?= h($user->email) ?></span>
        </div>
    </div>

    <?php
    $boxes = match ($role) {
        'admin' => [
            ['Users',          $counts['total'],     'fas fa-users',         'info',    'Total users'],
            ['In Progress',     $counts['in_progress'],'fas fa-pen-fancy',     'info',    'In Progress'],
            ['Awaiting QC',    $counts['qc'],        'fas fa-clipboard-check','warning','Digitized'],
            ['Completed',      $counts['completed'], 'fas fa-check-double',  'success', 'Completed jobs'],
        ],
        'scheduler' => [
            ['Scheduled',      $myCount,             'fas fa-calendar',      'primary', 'Scheduled jobs'],
            ['Awaiting QC',    $counts['qc'],        'fas fa-clipboard-check','warning','Awaiting QC'],
            ['In Production',  $counts['production'],'fas fa-industry',      'info',    'Production'],
            ['Completed',      $counts['completed'], 'fas fa-check-double',  'success', 'Completed'],
        ],
        'operator' => [
            ['My queue',       $myCount,             'fas fa-pen-fancy',     'info',    'In Progress'],
            ['Awaiting QC',    $counts['qc'],        'fas fa-clipboard-check','warning','Digitized'],
            ['Approved',       $counts['production'],'fas fa-thumbs-up',     'success', 'QC approved'],
            ['Completed',      $counts['completed'], 'fas fa-check-double',  'success', 'Completed'],
        ],
        'quality_checker' => [
            ['Review queue',   $myCount,             'fas fa-clipboard-check','warning','Awaiting QC'],
            ['Approved',       $counts['production'],'fas fa-thumbs-up',     'success', 'Approved'],
            ['In production',  $counts['production'],'fas fa-industry',      'info',    'Production'],
            ['Completed',      $counts['completed'], 'fas fa-check-double',  'success', 'Completed'],
        ],
        'production' => [
            ['My queue',       $myCount,             'fas fa-industry',      'success', 'Ready/Producing'],
            ['Approved',       $counts['production'],'fas fa-thumbs-up',     'info',    'Approved'],
            ['Awaiting QC',    $counts['qc'],        'fas fa-clipboard-check','warning','Awaiting QC'],
            ['Completed',      $counts['completed'], 'fas fa-check-double',  'success', 'Completed'],
        ],
        default => [
            ['Total',          $counts['total'],     'fas fa-list',          'secondary','All jobs'],
        ],
    };
    ?>
    <?php foreach ($boxes as $b): ?>
        <div class="col-md-3 col-sm-6">
            <div class="info-box mb-3 bg-<?= $b[3] ?>">
                <span class="info-box-icon"><i class="<?= $b[2] ?>"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?= h($b[0]) ?></span>
                    <span class="info-box-number"><?= h($b[1]) ?></span>
                    <div class="info-box-more small opacity-75"><?= h($b[4]) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="col-12">
        <div class="page-card card">
            <div class="card-header">
                <h3 class="card-title m-0"><i class="fas fa-stream me-2"></i>Recent jobs</h3>
                <?= $this->Html->link('<i class="fas fa-arrow-right me-1"></i>View all', ['controller' => 'Jobs', 'action' => 'index'], ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]) ?>
            </div>
            <div class="card-body p-0">
                <?php $hasRecent = false; foreach ($recent as $_) { $hasRecent = true; break; } ?>
                <?php if ($hasRecent): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 data-table">
                        <thead>
                            <tr>
                                <th>Job</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Organization</th>
                                <th>Scheduled</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent as $job): ?>
                            <tr>
                                <td>
                                    <?= $this->Html->link(h($job->job_number), ['controller' => 'Jobs', 'action' => 'view', $job->id], ['class' => 'job-link']) ?>
                                </td>
                                <td><?= h($job->title) ?></td>
                                <td>
                                    <?php $c = $statusColor[$job->status] ?? 'secondary'; ?>
                                    <span class="badge bg-<?= $c ?>"><?= $statusLabel($job->status) ?></span>
                                </td>
                                <td class="text-muted"><?= $job->hasValue('organization') ? h($job->organization->name) : '—' ?></td>
                                <td class="text-muted small"><?= $job->scheduled_date ? h($job->scheduled_date->format('M d, Y')) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <div>No jobs yet.</div>
                        <?php if (in_array($role, ['admin','scheduler'], true)): ?>
                            <?= $this->Html->link('<i class="fas fa-plus me-1"></i>Create the first job', ['controller' => 'Jobs', 'action' => 'add'], ['class' => 'btn btn-primary btn-sm mt-2', 'escape' => false]) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>