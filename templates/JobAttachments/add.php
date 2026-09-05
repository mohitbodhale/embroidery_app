<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\JobAttachment $jobAttachment
 */
$this->assign('title', 'Add attachment');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-paperclip me-2"></i>Add attachment</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($jobAttachment, ['type' => 'file']) ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Job</label>
                    <?= $this->Form->control('job_id', ['options' => $jobs, 'empty' => 'Select job', 'class' => 'form-select', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-6">
                    <label>Files <span class="text-muted small">(select one or more)</span></label>
                    <?= $this->Form->file('files[]', ['class' => 'form-control', 'multiple' => true, 'required' => true, 'accept' => '.pdf,.jpg,.jpeg,.png,.gif,.zip,.rar,.emb,.dst,.pes,.jef,.vp3,.xxx,.svg,.ai,.cdr,.eps,.tiff,.bmp']) ?>
                    <div class="form-text">PDF, images, archives, embroidery formats (max 20 MB each)</div>
                </div>
                <div class="col-md-6">
                    <label>Attachment type</label>
                    <?= $this->Form->control('file_type', ['class' => 'form-control', 'placeholder' => 'e.g. Design file, Reference photo', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-6">
                    <label>Notes (optional)</label>
                    <?= $this->Form->control('comments', ['class' => 'form-control', 'placeholder' => 'Any notes about these files', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-upload me-2"></i>Upload files', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>
