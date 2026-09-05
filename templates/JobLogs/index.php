<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\JobLog> $jobLogs
 */
$this->assign('title', 'Activity logs');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-stream me-2"></i>Activity logs</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Action</th>
                        <th>Comment</th>
                        <th>User</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jobLogs as $log): ?>
                    <tr>
                        <td class="text-muted"><?= $log->hasValue('job') ? h($log->job->job_number ?? ('Job #' . $log->job_id)) : 'Job #' . h($log->job_id) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= h(ucwords(str_replace('_', ' ', $log->action))) ?></span></td>
                        <td class="text-muted"><?= h($log->comments ?? '—') ?></td>
                        <td class="text-muted small"><?= $log->hasValue('user') ? h($log->user->name) : 'System' ?></td>
                        <td class="text-muted small"><?= $log->created_at ? h($log->created_at->format('M d, H:i')) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($jobLogs)): ?>
    <div class="card-footer">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-muted small mb-0"><?= $this->Paginator->counter('Page {{page}} of {{pages}} — {{count}} logs') ?></p>
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
