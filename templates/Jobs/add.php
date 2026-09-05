<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Job $job
 * @var \Cake\Collection\CollectionInterface|string[] $digitizers
 * @var \Cake\Collection\CollectionInterface|string[] $qcs
 * @var \Cake\Collection\CollectionInterface|string[] $organizations
 */
$this->assign('title', 'Create job');
?>
<div class="page-card card">
    <div class="card-body">
        <?= $this->Form->create($job, ['class' => 'job-add-form', 'type' => 'file']) ?>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Job number</label>
                <?= $this->Form->control('job_number', [
                    'class' => 'form-control',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
                <div class="form-text">Auto-generated. You can edit if needed.</div>
            </div>
            <div class="col-md-8 mb-3">
                <label>Title</label>
                <?= $this->Form->control('title', [
                    'class' => 'form-control',
                    'placeholder' => 'Brief description',
                    'required' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Organization</label>
                <?= $this->Form->control('organization_id', [
                    'options' => $organizations,
                    'class' => 'form-select',
                    'label' => false,
                    'empty' => 'Select organization',
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Scheduled date</label>
                <?= $this->Form->control('scheduled_date', [
                    'type' => 'date',
                    'class' => 'form-control',
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
                'placeholder' => 'Special instructions, color notes, sizing, etc.',
                'label' => false,
                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
            ]) ?>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Assign digitizer</label>
                <?= $this->Form->control('digitizer_id', [
                    'options' => $digitizers,
                    'empty' => 'Select digitizer',
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Assign quality checker</label>
                <?= $this->Form->control('qc_id', [
                    'options' => $qcs,
                    'empty' => 'Select QC',
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

         <?= $this->Form->hidden('status', ['value' => 'draft']) ?>

        <hr>

        <h4 class="mb-3"><i class="fas fa-paperclip me-2"></i>Attachments</h4>
        <div class="row g-3">
            <div class="col-12">
                <label>Files <span class="text-muted small">(select one or more)</span></label>
                <?= $this->Form->file('files[]', [
                    'class' => 'form-control',
                    'multiple' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}'],
                    'accept' => '.pdf,.jpg,.jpeg,.png,.gif,.zip,.rar,.emb,.dst,.pes,.jef,.vp3,.xxx,.svg,.ai,.cdr,.eps,.tiff,.bmp',
                ]) ?>
                <div class="form-text">PDF, images, archives, embroidery formats (max 20 MB each)</div>
            </div>
            <div class="col-md-6">
                <label>Attachment type (optional, applies to all)</label>
                <?= $this->Form->control('attachment_file_type', [
                    'class' => 'form-control',
                    'placeholder' => 'e.g. Design file, Reference photo',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}'],
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>Notes (optional, applies to all)</label>
                <?= $this->Form->control('attachment_comments', [
                    'class' => 'form-control',
                    'placeholder' => 'Any notes about these files',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}'],
                ]) ?>
            </div>
        </div>

        <div class="form-actions">
            <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Back to jobs', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
            <?= $this->Form->button('<i class="fas fa-save me-2"></i>Create job', [
                'class' => 'btn btn-primary',
                'type' => 'submit',
                'escapeTitle' => false,
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>