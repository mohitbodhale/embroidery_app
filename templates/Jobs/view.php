<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Job $job
 * @var array $statusMeta
 */
$statusInfo = $statusMeta[$job->status] ?? ['color' => '#6c757d', 'label' => $job->status, 'is_terminal' => false];
$this->assign('title', $job->job_number . ' · ' . $job->title);
?>
<div class="page-card card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom">
            <span class="text-muted small">Status</span>
            <span class="badge" style="background-color: <?= h($statusInfo['color']) ?>; color: white;"><?= h($statusInfo['label']) ?></span>
            <span class="ms-auto small text-muted">
                <?= $job->created_at ? 'Created ' . h($job->created_at->format('M d, Y')) : '' ?>
            </span>
        </div>

        <dl class="row mb-0 detail-list">
            <dt class="col-sm-3">Job number</dt>
            <dd class="col-sm-9 fw-semibold text-primary"><?= h($job->job_number) ?></dd>

            <dt class="col-sm-3">Title</dt>
            <dd class="col-sm-9"><?= h($job->title) ?></dd>

            <dt class="col-sm-3">Operator</dt>
            <dd class="col-sm-9">
                <?= $job->hasValue('operator') ? '<i class="fas fa-user me-1 text-muted"></i>' . h($job->operator->name) : '<span class="text-muted">—</span>' ?>
            </dd>

            <dt class="col-sm-3">Quality checker</dt>
            <dd class="col-sm-9">
                <?= $job->hasValue('qc') ? '<i class="fas fa-user-check me-1 text-muted"></i>' . h($job->qc->name) : '<span class="text-muted">—</span>' ?>
            </dd>

            <dt class="col-sm-3">Organization</dt>
            <dd class="col-sm-9">
                <?= $job->hasValue('organization') ? '<i class="fas fa-building me-1 text-muted"></i>' . h($job->organization->name) : '<span class="text-muted">—</span>' ?>
            </dd>

            <dt class="col-sm-3">Scheduled date</dt>
            <dd class="col-sm-9">
                <?= $job->scheduled_date ? '<i class="fas fa-calendar me-1 text-muted"></i>' . h($job->scheduled_date->format('M d, Y')) : '<span class="text-muted">—</span>' ?>
            </dd>
        </dl>

        <?php if (!empty($job->instructions)): ?>
        <div class="mt-4">
            <strong class="d-block mb-1 section-label"><i class="fas fa-list-ul me-1"></i>Instructions</strong>
            <blockquote class="detail-quote">
                <?= $this->Text->autoParagraph(h($job->instructions)); ?>
            </blockquote>
        </div>
        <?php endif; ?>

        <?php if (!empty($job->job_attachments)): ?>
        <div class="mt-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <strong class="section-label mb-0"><i class="fas fa-paperclip me-1"></i>Attachments</strong>
                <?php
                    $addPolicy = new \App\Policy\JobAttachmentPolicy();
                    $canAddAttachment = $addPolicy->canAdd($currentUser, $job);
                ?>
                <?php if ($canAddAttachment): ?>
                    <?= $this->Html->link('<i class="fas fa-plus me-1"></i>Add file', ['controller' => 'JobAttachments', 'action' => 'add', '?' => ['job_id' => $job->id, 'redirect' => '/jobs/view/' . $job->id]], ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]) ?>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($job->job_attachments as $att): ?>
                        <tr>
                            <td>
                                 <a href="<?= $this->Url->webroot(ltrim((string)$att->file_path, '/')) ?>" target="_blank" rel="noopener" class="job-link">
                                     <i class="fas fa-file me-1 text-muted"></i><?= h($att->file_name) ?>
                                 </a>
                             </td>
                             <td><span class="badge bg-light text-dark border"><?= h(strtoupper($att->file_type)) ?></span></td>
                             <td class="text-muted small"><?= $att->file_size ? $this->Number->toReadableSize($att->file_size) : '—' ?></td>
                             <td class="text-muted small"><?= $att->created_at ? h($att->created_at->format('M d, Y H:i')) : '—' ?></td>
                              <td class="text-end">
                                  <div class="row-actions">
                                       <a href="<?= $this->Url->build(['controller' => 'JobAttachments', 'action' => 'download', $att->id]) ?>" class="btn btn-icon btn-outline-info" title="Download"><i class="fas fa-download"></i></a>
                                      <?php if ($currentUser): ?>
                                          <?php $canDeleteAtt = false; ?>
                                          <?php $userRole = strtolower((string)$currentUser->role ?? ''); ?>
                                          <?php if (in_array($userRole, ['admin', 'scheduler'], true)): ?>
                                              <?php $canDeleteAtt = true; ?>
                                          <?php elseif ($att->uploaded_by == $currentUser->id): ?>
                                              <?php $canDeleteAtt = true; ?>
                                          <?php endif; ?>
                                          <?php if ($canDeleteAtt): ?>
                                              <form method="post" action="<?= $this->Url->build(['controller' => 'JobAttachments', 'action' => 'delete', $att->id]) ?>" style="display:inline" onsubmit="return confirm('Delete this file?')">
                                                 <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
                                                 <button class="btn btn-icon btn-outline-danger" type="submit" title="Delete"><i class="fas fa-trash"></i></button>
                                             </form>
                                          <?php endif; ?>
                                      <?php endif; ?>
                                  </div>
                              </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="mt-3 d-flex align-items-center justify-content-between">
            <span class="text-muted small">No attachments yet.</span>
            <?php
                $addPolicy = new \App\Policy\JobAttachmentPolicy();
                $canAddAttachment = $addPolicy->canAdd($currentUser, $job);
            ?>
            <?php if ($canAddAttachment): ?>
                <?= $this->Html->link('<i class="fas fa-plus me-1"></i>Add file', ['controller' => 'JobAttachments', 'action' => 'add', '?' => ['job_id' => $job->id, 'redirect' => '/jobs/view/' . $job->id]], ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($job->job_logs)): ?>
        <div class="mt-4">
            <strong class="d-block mb-3 section-label"><i class="fas fa-stream me-1"></i>Activity log</strong>
            <div class="activity-timeline">
                <?php foreach ($job->job_logs as $log): ?>
                    <?php
                        $action = strtolower((string)$log->action);
                        $icon = 'fa-circle';
                        $color = '#6c757d';
                        if (str_contains($action, 'note')) { $icon = 'fa-sticky-note'; $color = '#17a2b8'; }
                        elseif (str_contains($action, 'submit')) { $icon = 'fa-paper-plane'; $color = '#0dcaf0'; }
                        elseif (str_contains($action, 'approve')) { $icon = 'fa-check-circle'; $color = '#28a745'; }
                        elseif (str_contains($action, 'reject')) { $icon = 'fa-times-circle'; $color = '#dc3545'; }
                        elseif (str_contains($action, 'start')) { $icon = 'fa-play'; $color = '#ffc107'; }
                        elseif (str_contains($action, 'complete')) { $icon = 'fa-check-double'; $color = '#20c997'; }
                        elseif (str_contains($action, 'assign')) { $icon = 'fa-user-tag'; $color = '#6f42c1'; }
                        elseif (str_contains($action, 'upload')) { $icon = 'fa-file-upload'; $color = '#fd7e14'; }
                        elseif (str_contains($action, 'delete')) { $icon = 'fa-trash'; $color = '#dc3545'; }
                        elseif (str_contains($action, 'download')) { $icon = 'fa-download'; $color = '#0dcaf0'; }
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background-color: <?= h($color) ?>20; color: <?= h($color) ?>;">
                            <i class="fas <?= h($icon) ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="badge badge-action" style="background-color: <?= h($color) ?>; color: #fff;">
                                    <?= h(ucwords(str_replace('_', ' ', $log->action))) ?>
                                </span>
                                <span class="text-muted small ms-2">
                                    <?= $log->created_at ? h($log->created_at->format('M d, Y H:i')) : '—' ?>
                                </span>
                            </div>
                            <?php if (!empty($log->comments)): ?>
                                <div class="activity-text mt-1"><?= h($log->comments) ?></div>
                            <?php endif; ?>
                            <div class="activity-user text-muted small mt-1">
                                <i class="fas fa-user me-1"></i><?= $log->hasValue('user') ? h($log->user->name) : 'System' ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($currentRole) && in_array($currentRole, ['admin','scheduler','operator','quality_checker','production'], true)): ?>
        <div class="page-card card mt-3">
            <div class="card-header">
                <h3 class="card-title m-0"><i class="fas fa-plus-circle me-2"></i>Add note</h3>
            </div>
            <div class="card-body">
                <form method="post" action="<?= $this->Url->build(['action' => 'addLog', $job->id]) ?>">
                    <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label>Comment</label>
                            <?= $this->Form->control('comments', ['type' => 'textarea', 'class' => 'form-control', 'rows' => 2, 'placeholder' => 'Add a note about this job...', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" type="submit"><i class="fas fa-save me-1"></i>Save note</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($currentRole) && in_array($currentRole, ['admin','scheduler'], true)): ?>
<div class="page-card card mt-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-tag me-2"></i>Assignment</h3></div>
    <div class="card-body">
        <form method="post" action="<?= $this->Url->build(['action' => 'assign', $job->id]) ?>">
            <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label>Operator</label>
                     <?= $this->Form->select('operator_id', $operators, [
                        'empty' => 'Select Operator', 'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-5 mb-3">
                    <label>Quality checker</label>
                    <?= $this->Form->select('qc_id', $qcs, [
                        'empty' => 'Select QC', 'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-2 d-flex align-items-end mb-3">
                    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-save me-1"></i>Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($currentRole) && $currentRole === 'quality_checker' && $job->status === 'ready_for_qc'): ?>
<div class="page-card card mt-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check me-2"></i>QC Review</h3></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-7">
                <form method="post" action="<?= $this->Url->build(['action' => 'reject', $job->id]) ?>">
                    <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
                    <label>Reject with a comment</label>
                    <?= $this->Form->control('comment', ['type' => 'text', 'label' => false, 'required' => true, 'class' => 'form-control', 'placeholder' => 'Why is this being rejected?']) ?>
                    <button class="btn btn-danger mt-2" type="submit"><i class="fas fa-times me-1"></i>Reject</button>
                </form>
            </div>
            <div class="col-md-5 d-flex flex-column justify-content-end">
                <form method="post" action="<?= $this->Url->build(['action' => 'approve', $job->id]) ?>">
                    <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
                    <button class="btn btn-success w-100" type="submit"><i class="fas fa-check me-1"></i>Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($currentRole) && $currentRole === 'production' && in_array($job->status, ['qc_approved', 'in_production'], true)): ?>
<div class="alert-card success mt-3">
    <div><i class="fas fa-industry me-2"></i>Production action</div>
    <?php if ($job->status === 'qc_approved'): ?>
        <form method="post" action="<?= $this->Url->build(['action' => 'startProduction', $job->id]) ?>" style="display:inline">
            <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-play me-1"></i>Start Production</button>
        </form>
    <?php else: ?>
        <form method="post" action="<?= $this->Url->build(['action' => 'complete', $job->id]) ?>" style="display:inline">
            <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
            <button class="btn btn-success" type="submit"><i class="fas fa-check-double me-1"></i>Mark Completed</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="form-actions mt-4">
    <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Back to jobs', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
    <div class="d-flex gap-2">
        <?php if (!in_array($currentRole, ['operator'], true)): ?>
            <?= $this->Html->link('<i class="fas fa-pen me-1"></i>Edit', ['action' => 'edit', $job->id], ['class' => 'btn btn-primary', 'escape' => false]) ?>
            <form method="post" action="<?= $this->Url->build(['action' => 'delete', $job->id]) ?>" style="display:inline" onsubmit="return confirm('Delete job # <?= h($job->id) ?>?')">
                <input type="hidden" name="_csrfToken" value="<?= h($this->request->getAttribute('csrfToken')) ?>">
                <button class="btn btn-outline-danger" type="submit"><i class="fas fa-trash me-1"></i>Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>