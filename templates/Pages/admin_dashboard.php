<?php
/**
 * @var \App\View\AppView $this
 * @var array $kpi
 * @var array $usersByRole
 * @var array $jobsByStatus
 * @var array $workload
 * @var iterable $recentLogs
 * @var array $orgs
 */
$maxRoleCount = 1;
foreach ($usersByRole as $r) { $maxRoleCount = max($maxRoleCount, (int)($r['user_count'] ?? 0)); }
$maxStatusCount = 1;
foreach ($jobsByStatus as $s) { $maxStatusCount = max($maxStatusCount, (int)($s['job_count'] ?? 0)); }
?>
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div class="kpi-value"><?= h($kpi['users_total']) ?></div>
            <div class="kpi-label">Total Users</div>
            <div class="kpi-sub"><?= h($kpi['users_active']) ?> active</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="fas fa-briefcase"></i></div>
            <div class="kpi-value"><?= h($kpi['jobs_total']) ?></div>
            <div class="kpi-label">Total Jobs</div>
            <div class="kpi-sub"><?= h($kpi['jobs_open']) ?> open · <?= h($kpi['jobs_done']) ?> done</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="fas fa-paperclip"></i></div>
            <div class="kpi-value"><?= h($kpi['attachments']) ?></div>
            <div class="kpi-label">Attachments</div>
            <div class="kpi-sub">files uploaded</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-purple">
            <div class="kpi-icon"><i class="fas fa-history"></i></div>
            <div class="kpi-value"><?= h($kpi['logs_total']) ?></div>
            <div class="kpi-label">Activity Events</div>
            <div class="kpi-sub">across all jobs</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="page-card card h-100">
            <div class="card-header">
                <h3 class="card-title m-0"><i class="fas fa-user-shield me-2"></i>Users by Role</h3>
            </div>
            <div class="card-body">
                <?php if (empty($usersByRole)): ?>
                    <p class="text-muted mb-0">No roles defined.</p>
                <?php else: foreach ($usersByRole as $r):
                    $pct = $maxRoleCount > 0 ? round(((int)($r['user_count'] ?? 0) / $maxRoleCount) * 100) : 0;
                ?>
                    <div class="bar-row">
                        <div class="bar-label">
                            <span class="role-dot" style="background-color: <?= h($r['color'] ?? '#6c757d') ?>"></span>
                            <strong><?= h($r['label']) ?></strong>
                            <code class="text-muted small ms-1"><?= h($r['name']) ?></code>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $pct ?>%; background-color: <?= h($r['color'] ?? '#6c757d') ?>"></div>
                        </div>
                        <div class="bar-value"><?= h($r['user_count'] ?? 0) ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="page-card card h-100">
            <div class="card-header">
                <h3 class="card-title m-0"><i class="fas fa-tags me-2"></i>Jobs by Status</h3>
            </div>
            <div class="card-body">
                <?php if (empty($jobsByStatus)): ?>
                    <p class="text-muted mb-0">No statuses defined.</p>
                <?php else: foreach ($jobsByStatus as $s):
                    $pct = $maxStatusCount > 0 ? round(((int)($s['job_count'] ?? 0) / $maxStatusCount) * 100) : 0;
                ?>
                    <div class="bar-row">
                        <div class="bar-label">
                            <span class="status-dot" style="background-color: <?= h($s['color'] ?? '#6c757d') ?>"></span>
                            <strong><?= h($s['label']) ?></strong>
                            <?php if (!empty($s['is_terminal'])): ?><span class="badge bg-secondary ms-1">end</span><?php endif; ?>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $pct ?>%; background-color: <?= h($s['color'] ?? '#6c757d') ?>"></div>
                        </div>
                        <div class="bar-value"><?= h($s['job_count'] ?? 0) ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="page-card card">
            <div class="card-header">
                <h3 class="card-title m-0"><i class="fas fa-people-carry-box me-2"></i>User Workload</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table data-table mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th class="text-end">Created</th>
                                <th class="text-end">Digitizing</th>
                                <th class="text-end">QC</th>
                                <th class="text-end">Logs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workload as $w): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <?php if (!empty($w['avatar'])): ?>
                                                <img src="<?= $this->Url->webroot($w['avatar']) ?>" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;display:block;">
                                            <?php else: ?>
                                                <div class="user-avatar" style="background-color: <?= h($w['role_color'] ?? '#6c757d') ?>"><?= strtoupper(substr($w['name'] ?? 'U', 0, 1)) ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <div><strong><?= h($w['name']) ?></strong></div>
                                                <div class="text-muted small"><?= h($w['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= h($w['role_color'] ?? '#6c757d') ?>"><?= h($w['role_label'] ?? $w['role_name'] ?? '—') ?></span>
                                    </td>
                                    <td class="text-end"><?= h($w['jobs_created']) ?></td>
                                    <td class="text-end">                                    <?= h($w['jobs_operator']) ?></td>
                                    <td class="text-end"><?= h($w['jobs_qc']) ?></td>
                                    <td class="text-end"><?= h($w['log_entries']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="page-card card h-100">
            <div class="card-header">
                <h3 class="card-title m-0"><i class="fas fa-clock-rotate-left me-2"></i>Recent Activity</h3>
            </div>
            <div class="card-body">
                <?php $logCount = ($recentLogs instanceof \Countable || is_iterable($recentLogs)) ? iterator_count($recentLogs) : 0; ?>
                <?php if ($logCount === 0): ?>
                    <p class="text-muted mb-0">No activity yet.</p>
                <?php else: foreach ($recentLogs as $log): ?>
                    <div class="activity-item">
                        <div class="activity-icon"><i class="fas fa-circle-dot"></i></div>
                        <div class="activity-body">
                            <div class="activity-title">
                                <strong><?= h($log->user->name ?? 'System') ?></strong>
                                <span class="text-muted">on</span>
                                <?= h($log->job->title ?? 'Job #' . $log->job_id) ?>
                            </div>
                            <div class="activity-meta text-muted small">
                                <?= h($log->action) ?> · <?= h($log->created_at?->format('M j, g:i a') ?? '') ?>
                            </div>
                            <?php if (!empty($log->comments)): ?>
                                <div class="activity-text small"><?= h($log->comments) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
