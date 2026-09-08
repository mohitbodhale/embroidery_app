<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Job> $jobs
 * @var array $statusMeta
 * @var string $role
 * @var string|null $statusFilter
 */
$this->assign('title', 'Jobs');
$canCreate = !empty($currentRole) && in_array($currentRole, ['admin', 'scheduler'], true);

$isOperator = in_array($role, ['operator', 'quality_checker', 'production'], true);
$filterTabs = [];
                            if ($role === 'operator') {
    $filterTabs = [
        'in_progress' => 'In Progress',
        'sent_for_qc' => 'Sent for QC',
        'done' => 'Done',
    ];
} elseif ($role === 'quality_checker') {
    $filterTabs = [
        'in_progress' => 'In Progress',
        'done' => 'Done',
    ];
} elseif ($role === 'production') {
    $filterTabs = [
        'in_progress' => 'In Progress',
        'done' => 'Done',
    ];
}
?>

<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-briefcase me-2"></i>Jobs</h3>
        <?php if ($canCreate): ?>
            <?= $this->Html->link('<i class="fas fa-plus me-1"></i>New job', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
        <?php endif; ?>
    </div>
    <?php if (!empty($filterTabs)): ?>
    <div class="card-body border-bottom">
        <ul class="nav nav-pills gap-2">
            <li class="nav-item">
                <a class="nav-link <?= empty($statusFilter) ? 'active' : '' ?>" href="<?= $this->Url->build(['action' => 'index']) ?>">All</a>
            </li>
            <?php foreach ($filterTabs as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($statusFilter === $key) ? 'active' : '' ?>" href="<?= $this->Url->build(['action' => 'index', '?' => ['status' => $key]]) ?>"><?= h($label) ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <div class="card-body p-0">
        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p class="mb-1 fw-semibold">No jobs found</p>
                <p class="text-muted small mb-0">Jobs you create or get assigned will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th><?= $this->Paginator->sort('job_number', 'Job #') ?></th>
                            <th><?= $this->Paginator->sort('title') ?></th>
                            <th><?= $this->Paginator->sort('status') ?></th>
                            <th>                            <?= $this->Paginator->sort('operator_id', 'Operator') ?></th>
                            <th><?= $this->Paginator->sort('scheduled_date', 'Scheduled') ?></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <?php
                            $statusInfo = $statusMeta[$job->status] ?? ['color' => '#6c757d', 'label' => $job->status];
                            $isInProgress = false;
                            if ($role === 'operator') {
                                $isInProgress = in_array($job->status, ['in_digitizing', 'qc_rejected'], true);
                            } elseif ($role === 'quality_checker') {
                                $isInProgress = $job->status === 'digitized';
                            } elseif ($role === 'production') {
                                $isInProgress = $job->status === 'in_production';
                            }
                        ?>
                        <tr>
                            <td>
                                <?= $this->Html->link(h($job->job_number), ['action' => 'view', $job->id], ['class' => 'job-link']) ?>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:280px" title="<?= h($job->title) ?>">
                                    <?= h($job->title) ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-dot" style="background-color: <?= h($statusInfo['color']) ?>"></span>
                                <span class="status-label"><?= h($statusInfo['label']) ?></span>
                            </td>
                            <td>
                                <?= $job->hasValue('operator')
                                    ? '<i class="fas fa-user me-1 text-muted"></i>' . h($job->operator->name)
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td>
                                <?php if ($job->scheduled_date): ?>
                                    <i class="fas fa-calendar me-1 text-muted"></i><?= h($job->scheduled_date->format('M d, Y')) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="row-actions">
                                    <?= $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $job->id], ['class' => 'btn btn-icon btn-outline-info', 'escape' => false, 'title' => 'View']) ?>
                                    <?php if ($isInProgress): ?>
                                        <?= $this->Html->link('<i class="fas fa-pen"></i>', ['action' => 'edit', $job->id], ['class' => 'btn btn-icon btn-outline-primary', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($jobs)): ?>
    <div class="card-footer">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-muted small mb-0">
                <?= $this->Paginator->counter('Showing {{current}} of {{count}} jobs') ?>
            </p>
            <ul class="pagination pagination-sm mb-0">
                <?= $this->Paginator->first('«') ?>
                <?= $this->Paginator->prev('‹') ?>
                <?= $this->Paginator->numbers(['currentClass' => 'active', 'currentTag' => 'span']) ?>
                <?= $this->Paginator->next('›') ?>
                <?= $this->Paginator->last('»') ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>