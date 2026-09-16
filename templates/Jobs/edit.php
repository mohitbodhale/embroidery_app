<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Job $job
 * @var string[]|\Cake\Collection\CollectionInterface $operators
 * @var string[]|\Cake\Collection\CollectionInterface $qcs
 * @var string[]|\Cake\Collection\CollectionInterface $organizations
 * @var string[]|\Cake\Collection\CollectionInterface $statuses
 * @var array $statusMeta
 */
$currentStatusMeta = $statusMeta[$job->status] ?? ['color' => '#6c757d', 'label' => $job->status, 'is_terminal' => false];
$this->assign('title', 'Edit job ' . $job->job_number);
?>
<div class="page-card card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
            <span class="text-muted small">Status</span>
            <span class="badge" style="background-color: <?= h($currentStatusMeta['color']) ?>; color: white;"><?= h($currentStatusMeta['label']) ?></span>
            <span class="text-muted small ms-auto">Created <?= $job->created_at ? h($job->created_at->format('M d, Y')) : '—' ?></span>
        </div>

        <?= $this->Form->create($job, ['class' => 'job-edit-form', 'type' => 'file']) ?>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Job number</label>
                <?= $this->Form->control('job_number', [
                    'class' => 'form-control',
                    'required' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-8 mb-3">
                <label>Title</label>
                <?= $this->Form->control('title', [
                    'class' => 'form-control',
                    'required' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="mb-3">
            <label>Instructions</label>
            <?= $this->Form->control('instructions', [
                'type' => 'textarea',
                'class' => 'form-control',
                'rows' => 4,
                'label' => false,
                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
            ]) ?>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Status</label>
                <?php if ($currentRole !== 'operator'): ?>
                <?= $this->Form->control('status', [
                    'options' => $statuses,
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
                <?php else: ?>
                <div class="form-control" style="background:#f8f9fa;color:#6c757d;cursor:default;"><?= h($statusMeta[$job->status]['label'] ?? $job->status) ?></div>
                <?= $this->Form->hidden('status', ['value' => $job->status]) ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label>Operator</label>
                <?php if ($currentRole !== 'operator'): ?>
                <?= $this->Form->control('operator_id', [
                    'options' => $operators,
                    'empty' => 'Unassigned',
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
                <?php else: ?>
                <div class="form-control" style="background:#f8f9fa;color:#6c757d;cursor:default;"><?= h($job->hasValue('operator') ? $job->operator->name : 'Unassigned') ?></div>
                <?= $this->Form->hidden('operator_id', ['value' => $job->operator_id ?? '']) ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label>Quality checker</label>
                <?php if ($currentRole !== 'operator'): ?>
                <?= $this->Form->control('qc_id', [
                    'options' => $qcs,
                    'empty' => 'Unassigned',
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
                <?php else: ?>
                <div class="form-control" style="background:#f8f9fa;color:#6c757d;cursor:default;"><?= h($job->hasValue('qc') ? $job->qc->name : 'Unassigned') ?></div>
                <?= $this->Form->hidden('qc_id', ['value' => $job->qc_id ?? '']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <?php if ($currentRole !== 'operator'): ?>
            <div class="col-md-6 mb-3">
                <label>Scheduled date</label>
                <?= $this->Form->control('scheduled_date', [
                    'type' => 'date',
                    'class' => 'form-control',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Organization</label>
                <?= $this->Form->control('organization_id', [
                    'options' => $organizations,
                    'class' => 'form-select',
                    'label' => false,
                    'empty' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <?php endif; ?>
        </div>

        <hr>

        <?php if ($canAddAttachment || !empty($job->job_attachments)): ?>
        <div class="attachment-section mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0"><i class="fas fa-paperclip me-2"></i>Attachments</h4>
                <?php if ($canAddAttachment): ?>
                    <span class="badge bg-primary-subtle text-primary fw-semibold"><i class="fas fa-plus-circle me-1"></i>Upload</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($job->job_attachments)): ?>
            <div class="table-responsive mb-3">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded by</th>
                            <th>When</th>
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
                            <td class="text-muted small"><?= $att->hasValue('uploaded_by_user') ? h($att->uploaded_by_user->name) : 'System' ?></td>
                            <td class="text-muted small"><?= $att->created_at ? h($att->created_at->format('M d, Y H:i')) : '—' ?></td>
                            <td class="text-end">
                                <div class="row-actions">
                                    <a href="<?= $this->Url->build(['controller' => 'JobAttachments', 'action' => 'download', $att->id]) ?>" class="btn btn-icon btn-outline-info" title="Download"><i class="fas fa-download"></i></a>
                                    <?php if ($currentUser): ?>
                                        <?php $canDeleteAtt = false; ?>
                                        <?php $userRole = strtolower((string)($currentUser->role ?? '')); ?>
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
            <?php endif; ?>

            <?php if ($canAddAttachment): ?>
            <div class="card border-primary border-2 opacity-75">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-cloud-upload-alt text-primary me-2 fs-4"></i>
                        <h5 class="card-title mb-0 text-primary">Upload Files</h5>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Add more files</label>
                            <?= $this->Form->file('files[]', [
                                'class' => 'form-control form-control-lg',
                                'multiple' => true,
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                                'accept' => '.pdf,.jpg,.jpeg,.png,.gif,.zip,.rar,.emb,.dst,.pes,.jef,.vp3,.xxx,.svg,.ai,.cdr,.eps,.tiff,.bmp',
                            ]) ?>
                            <div class="form-text">PDF, images, archives, embroidery formats (max 20 MB each)</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Attachment type</label>
                            <?= $this->Form->control('attachment_file_type', [
                                'class' => 'form-control',
                                'placeholder' => 'e.g. Design file',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <?= $this->Form->control('attachment_comments', [
                                'class' => 'form-control',
                                'placeholder' => 'Any notes',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" style="height:48px;">
                                <i class="fas fa-upload me-1"></i>Upload
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?= $this->Form->hidden('created_by') ?>

        <div class="form-actions">
            <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Back', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
            <div class="d-flex gap-2">
                <?php if ($currentRole !== 'operator'): ?>
                    <?php if ($canDelete): ?>
                        <?= $this->Form->postLink('<i class="fas fa-trash me-1"></i>Delete', ['action' => 'delete', $job->id], [
                            'confirm' => __('Delete job # {0}?', $job->id),
                            'class' => 'btn btn-outline-danger',
                            'escape' => false,
                        ]) ?>
                    <?php endif; ?>
                    <?= $this->Form->button('<i class="fas fa-save me-2"></i>Save changes', [
                        'class' => 'btn btn-primary',
                        'type' => 'submit',
                        'escapeTitle' => false,
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
        <?php if ($currentRole === 'operator'): ?>
        <div class="form-actions">
            <div class="d-flex gap-2">
                <?= $this->Form->postLink('<i class="fas fa-paper-plane me-1"></i>Send to QC', ['action' => 'submit', $job->id], ['class' => 'btn btn-primary', 'escape' => false, 'confirm' => __('Submit this job for QC review?')]) ?>
                <?= $this->Form->postLink('<i class="fas fa-undo me-1"></i>Return to Scheduler', ['action' => 'returnToScheduler', $job->id], ['class' => 'btn btn-outline-secondary', 'escape' => false, 'confirm' => __('Return this job to the scheduler?')]) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>