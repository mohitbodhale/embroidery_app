<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\JobStatus> $jobStatuses
 */
$this->assign('title', 'Job Statuses');
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-tags me-2"></i>Job Statuses</h3>
        <div class="d-flex gap-2">
            <?= $this->Html->link('<i class="fas fa-file-csv me-1"></i>Export CSV', ['action' => 'export'], ['class' => 'btn btn-success btn-sm', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus me-1"></i>New Status', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($jobStatuses) || iterator_count($jobStatuses) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h4>No statuses defined yet</h4>
                <p>Define the workflow statuses your jobs will move through.</p>
                <?= $this->Html->link('Add Status', ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Label</th>
                            <th>Description</th>
                            <th>Color</th>
                            <th>Active</th>
                            <th>Terminal</th>
                            <th>Jobs</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobStatuses as $jobStatus): ?>
                            <tr>
                                <td><?= h($jobStatus->sort_order) ?></td>
                                <td><code><?= h($jobStatus->name) ?></code></td>
                                <td><strong><?= h($jobStatus->label) ?></strong></td>
                                <td class="text-muted small"><?= h($jobStatus->description) ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= h($jobStatus->color) ?>; color: white;">
                                        <?= h($jobStatus->color) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($jobStatus->is_active): ?>
                                        <span class="status-dot status-dot-completed"></span> Active
                                    <?php else: ?>
                                        <span class="status-dot status-dot-rejected"></span> Inactive
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($jobStatus->is_terminal): ?>
                                        <span class="badge bg-secondary">End</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= count($jobStatus->jobs ?? []) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="row-actions">
                                        <?= $this->Html->link('<i class="fas fa-pen"></i>', ['action' => 'edit', $jobStatus->id], ['class' => 'btn-icon', 'escape' => false, 'title' => 'Edit']) ?>
                                        <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $jobStatus->id], ['class' => 'btn-icon btn-icon-danger', 'escape' => false, 'title' => 'Delete', 'confirm' => __('Delete status "{0}"?', $jobStatus->label)]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
