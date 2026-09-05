<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\JobAttachment $jobAttachment
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?php if ($currentUser && $this->authorizeAction($jobAttachment, 'edit')): ?>
                <?= $this->Html->link(__('Edit Job Attachment'), ['action' => 'edit', $jobAttachment->id], ['class' => 'side-nav-item']) ?>
            <?php endif; ?>
            <?php if ($currentUser && $this->authorizeAction($jobAttachment, 'delete')): ?>
                <?= $this->Form->postLink(__('Delete Job Attachment'), ['action' => 'delete', $jobAttachment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $jobAttachment->id), 'class' => 'side-nav-item']) ?>
            <?php endif; ?>
            <?= $this->Html->link(__('List Job Attachments'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?php if ($currentUser && $this->authorizeAction('JobAttachment', 'add')): ?>
                <?= $this->Html->link(__('New Job Attachment'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
            <?php endif; ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="jobAttachments view content">
            <h3><?= h($jobAttachment->file_type) ?></h3>
            <table>
                <tr>
                    <th><?= __('Job') ?></th>
                    <td><?= $jobAttachment->hasValue('job') ? $this->Html->link($jobAttachment->job->title, ['controller' => 'Jobs', 'action' => 'view', $jobAttachment->job->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('File Type') ?></th>
                    <td><?= h($jobAttachment->file_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('File Name') ?></th>
                    <td><?= h($jobAttachment->file_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($jobAttachment->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Uploaded By') ?></th>
                    <td><?= $jobAttachment->uploaded_by === null ? '' : $this->Number->format($jobAttachment->uploaded_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created At') ?></th>
                    <td><?= h($jobAttachment->created_at) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('File Path') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($jobAttachment->file_path)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>