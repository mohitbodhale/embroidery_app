<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Role> $roles
 */
$this->assign('title', 'Roles');
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-user-shield me-2"></i>Roles</h3>
        <div class="d-flex gap-2">
            <?= $this->Html->link('<i class="fas fa-file-csv me-1"></i>Export CSV', ['action' => 'export'], ['class' => 'btn btn-success btn-sm', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus me-1"></i>New Role', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($roles) || iterator_count($roles) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-user-shield"></i>
                <h4>No roles defined yet</h4>
                <p>Create your first role to get started.</p>
                <?= $this->Html->link('Add Role', ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
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
                            <th>Status</th>
                            <th>Users</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?= h($role->sort_order) ?></td>
                                <td><code><?= h($role->name) ?></code></td>
                                <td><strong><?= h($role->label) ?></strong></td>
                                <td class="text-muted small"><?= h($role->description) ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= h($role->color) ?>; color: white;">
                                        <?= h($role->color) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($role->is_active): ?>
                                        <span class="status-dot status-dot-completed"></span> Active
                                    <?php else: ?>
                                        <span class="status-dot status-dot-rejected"></span> Inactive
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= count($role->users ?? []) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="row-actions">
                                        <?= $this->Html->link('<i class="fas fa-pen"></i>', ['action' => 'edit', $role->id], ['class' => 'btn-icon', 'escape' => false, 'title' => 'Edit']) ?>
                                        <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $role->id], ['class' => 'btn-icon btn-icon-danger', 'escape' => false, 'title' => 'Delete', 'confirm' => __('Delete role "{0}"?', $role->label)]) ?>
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
