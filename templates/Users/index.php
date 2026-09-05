<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
$this->assign('title', 'Users');
$roleColors = [
    'admin' => 'danger',
    'scheduler' => 'primary',
    'digitizer' => 'info',
    'quality_checker' => 'warning',
    'production' => 'success',
    'pending' => 'secondary',
];
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-users me-2"></i>Team members</h3>
        <div class="d-flex gap-2">
            <?= $this->Html->link('<i class="fas fa-file-csv me-1"></i>Export CSV', ['action' => 'export'], ['class' => 'btn btn-success btn-sm', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus me-1"></i>New user', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= $this->Paginator->sort('email') ?></th>
                        <th><?= $this->Paginator->sort('role') ?></th>
                        <th><?= $this->Paginator->sort('created_at', 'Joined') ?></th>
                        <th>Organization</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <?php if (!empty($user->user_detail->avatar)): ?>
                                    <img src="<?= $this->Url->webroot($user->user_detail->avatar) ?>" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;display:block;">
                                <?php else: ?>
                                    <div class="user-avatar"><?= strtoupper(substr($user->name ?? '?', 0, 1)) ?></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= h($user->name) ?></div>
                                    <div class="text-muted small">#<?= h($user->id) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-muted"><?= h($user->email) ?></span></td>
                        <td>
                            <?php $cls = $roleColors[$user->role] ?? 'secondary'; ?>
                            <span class="badge bg-<?= $cls ?>"><?= h(ucwords(str_replace('_', ' ', $user->role))) ?></span>
                        </td>
                        <td><?= $user->created_at ? h($user->created_at->format('M d, Y')) : '—' ?></td>
                        <td><?= $user->hasValue('organization') ? h($user->organization->name) : '—' ?></td>
                        <td class="text-end">
                            <div class="row-actions">
                                <?= $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $user->id], ['class' => 'btn btn-icon btn-outline-info', 'escape' => false, 'title' => 'View']) ?>
                                <?= $this->Html->link('<i class="fas fa-pen"></i>', ['action' => 'edit', $user->id], ['class' => 'btn btn-icon btn-outline-primary', 'escape' => false, 'title' => 'Edit']) ?>
                                <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $user->id], [
                                    'method' => 'delete',
                                    'confirm' => __('Delete user # {0}?', $user->id),
                                    'class' => 'btn btn-icon btn-outline-danger',
                                    'escape' => false,
                                    'title' => 'Delete',
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($users)): ?>
    <div class="card-footer">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-muted small mb-0">
                <?= $this->Paginator->counter('Page {{page}} of {{pages}} — {{count}} users') ?>
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