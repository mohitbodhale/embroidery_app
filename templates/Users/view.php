<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$roleColors = [
    'admin' => 'danger',
    'scheduler' => 'primary',
    'digitizer' => 'info',
    'quality_checker' => 'warning',
    'production' => 'success',
    'pending' => 'secondary',
];
$this->assign('title', $user->name);
?>
<div class="profile-page">
    <div class="profile-header">
        <div class="profile-cover">
            <div class="cover-pattern"></div>
        </div>
        <div class="profile-header-content">
            <div class="profile-avatar-wrap">
                <?php if (!empty($user->user_detail->avatar)): ?>
                    <img src="<?= $this->Url->webroot($user->user_detail->avatar) ?>" alt="Avatar" class="profile-avatar-img">
                <?php else: ?>
                    <div class="profile-avatar"><?= strtoupper(substr($user->name ?? '?', 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-header-info">
                <h1 class="profile-name"><?= h($user->name) ?></h1>
                <p class="profile-email"><?= h($user->email) ?></p>
                <?php $cls = $roleColors[$user->role] ?? 'secondary'; ?>
                <span class="badge bg-<?= $cls ?> profile-role-badge"><?= h(ucwords(str_replace('_', ' ', $user->role))) ?></span>
            </div>
            <div class="profile-header-actions">
                <?= $this->Html->link('<i class="fas fa-pen me-1"></i>Edit', ['action' => 'edit', $user->id], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
                <?= $this->Form->postLink('<i class="fas fa-key me-1"></i>Reset Password', ['action' => 'resetPassword', $user->id], [
                    'confirm' => __('Generate a new temporary password for {0}?', $user->name),
                    'class' => 'btn btn-warning btn-sm',
                    'escape' => false,
                ]) ?>
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Back', ['action' => 'index'], ['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false]) ?>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="page-card card">
                    <div class="card-header">
                        <h3 class="card-title m-0"><i class="fas fa-user me-2"></i>Profile Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">User ID</div>
                                <div class="info-value">#<?= h($user->id) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= h($user->email) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Role</div>
                                <div class="info-value">
                                    <?php $cls = $roleColors[$user->role] ?? 'secondary'; ?>
                                    <span class="badge bg-<?= $cls ?>"><?= h(ucwords(str_replace('_', ' ', $user->role))) ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Organization</div>
                                <div class="info-value"><?= $user->hasValue('organization') ? h($user->organization->name) : '—' ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Joined</div>
                                <div class="info-value"><?= $user->created_at ? h($user->created_at->format('M d, Y')) : '—' ?></div>
                            </div>
                            <?php if (!empty($user->user_detail->phone)): ?>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?= h($user->user_detail->phone) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($user->user_detail->location)): ?>
                            <div class="info-item">
                                <div class="info-label">Location</div>
                                <div class="info-value"><?= h($user->user_detail->location) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($user->user_detail->website)): ?>
                            <div class="info-item">
                                <div class="info-label">Website</div>
                                <div class="info-value"><?= $this->Html->link(h($user->user_detail->website), $user->user_detail->website, ['target' => '_blank']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($user->user_detail->bio)): ?>
                            <div class="info-item col-12">
                                <div class="info-label">Bio</div>
                                <div class="info-value bio-text"><?= nl2br(h($user->user_detail->bio)) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="page-card card">
                    <div class="card-header">
                        <h3 class="card-title m-0"><i class="fas fa-clock me-2"></i>Recent Activity</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($user->job_logs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 data-table">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Comments</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach (array_slice($user->job_logs, 0, 5) as $log): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border"><?= h($log->action) ?></span></td>
                                        <td class="text-muted"><?= h($log->comments ?? '—') ?></td>
                                        <td class="text-muted small"><?= $log->created_at ? h($log->created_at->format('M d, H:i')) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">No recent activity</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
