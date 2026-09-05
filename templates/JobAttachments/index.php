<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\JobAttachment> $jobAttachments
 */
$this->assign('title', 'Attachments');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-paperclip me-2"></i>Attachments</h3>
        <?= $this->Html->link('<i class="fas fa-plus me-1"></i>Add attachment', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Job</th>
                        <th>Type</th>
                        <th>Uploaded by</th>
                        <th>When</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jobAttachments as $att): ?>
                    <tr>
                        <td>
                             <a href="<?= $this->Url->webroot(ltrim((string)$att->file_path, '/')) ?>" target="_blank" rel="noopener" class="job-link">
                                <i class="fas fa-file me-1 text-muted"></i><?= h($att->file_name) ?>
                            </a>
                        </td>
                        <td class="text-muted"><?= $att->hasValue('job') ? h($att->job->job_number ?? $att->job->title) : '—' ?></td>
                        <td><span class="badge bg-light text-dark border"><?= h(strtoupper($att->file_type)) ?></span></td>
                        <td class="text-muted small"><?= $att->hasValue('uploaded_by_user') ? h($att->uploaded_by_user->name) : '—' ?></td>
                        <td class="text-muted small"><?= $att->created_at ? h($att->created_at->format('M d, Y H:i')) : '—' ?></td>
                        <td class="text-end">
                            <div class="row-actions">
                                <a href="<?= $this->Url->build(['controller' => 'JobAttachments', 'action' => 'download', $att->id]) ?>" class="btn btn-icon btn-outline-info" title="Download"><i class="fas fa-download"></i></a>
                                <?php if ($currentUser): ?>
                                    <?php $canEdit = $this->authorizeAction($att, 'edit'); ?>
                                    <?php if ($canEdit): ?>
                                        <a href="<?= $this->Url->build(['action' => 'edit', $att->id]) ?>" class="btn btn-icon btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                                    <?php endif; ?>
                                    <?php $canDelete = $this->authorizeAction($att, 'delete'); ?>
                                    <?php if ($canDelete): ?>
                                        <form method="post" action="<?= $this->Url->build(['action' => 'delete', $att->id]) ?>" style="display:inline" onsubmit="return confirm('Delete attachment?')">
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
    <?php if (!empty($jobAttachments)): ?>
    <div class="card-footer">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="text-muted small mb-0"><?= $this->Paginator->counter('Page {{page}} of {{pages}} — {{count}} attachments') ?></p>
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
