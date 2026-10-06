<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Job $job
 * @var \Cake\Collection\CollectionInterface|string[] $operators
 * @var \Cake\Collection\CollectionInterface|string[] $qcs
 * @var \Cake\Collection\CollectionInterface|string[] $organizations
 * @var array<int|string, string> $levels
 * @var array<int|string, array{base: float, periods: array<int, array{from: string|null, to: string|null, amount: float}>} $rateSchedule
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
                <label>Assign operator</label>
                <?= $this->Form->control('operator_id', [
                    'options' => $operators,
                    'empty' => 'Select operator',
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

         <div class="row">
            <div class="col-md-6 mb-3">
                <label>Job level</label>
                <?= $this->Form->control('level_id', [
                    'options' => $levels,
                    'empty' => 'No level',
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
                <div class="form-text">The payment is computed live from the rate period covering the scheduled date. Rates are maintained under Levels (sidebar &rarr; Masters).</div>
            </div>
            <div class="col-md-6 mb-3">
                <label>Payment for this job</label>
                <div class="form-control" style="background:#f8f9fa;color:#6c757d;cursor:default;" id="level-payment-display">—</div>
                <div class="form-text">Pick a level and a scheduled date to preview the rate the job will pay.</div>
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
<?php $this->start('script'); ?>
<script>
(function () {
    var schedule = <?= json_encode($rateSchedule) ?>;
    var levelSelect = document.getElementById('level_id');
    var dateInput = document.getElementById('scheduled-date');
    var display = document.getElementById('level-payment-display');
    if (!display) {
        return;
    }
    function today() {
        var now = new Date();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        return now.getFullYear() + '-' + month + '-' + day;
    }
    // Mirrors LevelRatesTable::rateFor(): among the periods
    // covering the day, the one with the latest start wins;
    // otherwise the level's base amount applies.
    function rateForDay(level, day) {
        var best = null;
        var bestFrom = null;
        for (var i = 0; i < level.periods.length; i++) {
            var period = level.periods[i];
            if (period.from && period.from > day) { continue; }
            if (period.to && period.to < day) { continue; }
            var from = period.from || '0000-00-00';
            if (best === null || from >= bestFrom) {
                best = period;
                bestFrom = from;
            }
        }
        if (best !== null) {
            return Number(best.amount).toFixed(2);
        }
        return Number(level.base).toFixed(2);
    }
    function refresh() {
        var level = levelSelect && schedule[levelSelect.value] ? schedule[levelSelect.value] : null;
        if (!level) {
            display.textContent = '—';
            return;
        }
        var day = (dateInput && dateInput.value) || today();
        display.textContent = rateForDay(level, day);
    }
    if (levelSelect) {
        levelSelect.addEventListener('change', refresh);
    }
    if (dateInput) {
        dateInput.addEventListener('change', refresh);
    }
    refresh();
})();
</script>
<?php $this->end(); ?>