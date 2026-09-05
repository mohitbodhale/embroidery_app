<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $jobLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Job Log'), ['action' => 'edit', $jobLog->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Job Log'), ['action' => 'delete', $jobLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $jobLog->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Job Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Job Log'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="jobLogs view content">
            <h3><?= h($jobLog->action) ?></h3>
            <table>
                <tr>
                    <th><?= __('Action') ?></th>
                    <td><?= h($jobLog->action) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($jobLog->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Job Id') ?></th>
                    <td><?= $jobLog->job_id === null ? '' : $this->Number->format($jobLog->job_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User Id') ?></th>
                    <td><?= $jobLog->user_id === null ? '' : $this->Number->format($jobLog->user_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created At') ?></th>
                    <td><?= h($jobLog->created_at) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Comments') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($jobLog->comments)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>