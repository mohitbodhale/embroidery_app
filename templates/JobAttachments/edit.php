<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\JobAttachment $jobAttachment
 * @var string[]|\Cake\Collection\CollectionInterface $jobs
 */
$this->assign('title', 'Edit attachment');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-pen me-2"></i>Edit attachment</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($jobAttachment, ['type' => 'file']) ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Job</label>
                    <?= $this->Form->control('job_id', ['options' => $jobs, 'empty' => 'Select job', 'class' => 'form-select', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-6">
                    <label>Replace file (optional)</label>
                    <?= $this->Form->file('files[]', ['class' => 'form-control', 'accept' => '.pdf,.jpg,.jpeg,.png,.gif,.zip,.rar,.emb,.dst,.pes,.jef,.vp3,.xxx,.svg,.ai,.cdr,.eps,.tiff,.bmp']) ?>
                    <div class="form-text">Leave empty to keep the current file.</div>
                </div>
                <div class="col-md-6">
                    <label>Attachment type</label>
                    <?= $this->Form->control('file_type', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-6">
                    <label>Notes (optional)</label>
                    <?= $this->Form->control('comments', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-2"></i>Save', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>
