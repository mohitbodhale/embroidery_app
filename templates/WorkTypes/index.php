<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\WorkType> $workTypes
 */
$this->assign('title', 'Work Types');
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-tools me-2"></i>Work Types</h3>
        <div class="d-flex gap-2">
            <?= $this->Html->link('<i class="fas fa-plus me-1"></i>New Work Type', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($workTypes) || iterator_count($workTypes) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-tools"></i>
                <h4>No work types defined yet</h4>
                <p>Define the work types for operators (digitizing, programming, data entry).</p>
                <?= $this->Html->link('Add Work Type', ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
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
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workTypes as $workType): ?>
                            <tr>
                                <td><?= h($workType->sort_order) ?></td>
                                <td><code><?= h($workType->name) ?></code></td>
                                <td><strong><?= h($workType->label) ?></strong></td>
                                <td class="text-muted small"><?= h($workType->description) ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= h($workType->color) ?>; color: white;">
                                        <?= h($workType->color) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($workType->is_active): ?>
                                        <span class="status-dot status-dot-completed"></span> Active
                                    <?php else: ?>
                                        <span class="status-dot status-dot-rejected"></span> Inactive
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="row-actions">
                                        <?= $this->Html->link('<i class="fas fa-pen"></i>', ['action' => 'edit', $workType->id], ['class' => 'btn-icon', 'escape' => false, 'title' => 'Edit']) ?>
                                        <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $workType->id], ['class' => 'btn-icon btn-icon-danger', 'escape' => false, 'title' => 'Delete', 'confirm' => __('Delete work type "{0}"?', $workType->label)]) ?>
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
