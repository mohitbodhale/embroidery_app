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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $jobLog->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $jobLog->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Job Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="jobLogs form content">
            <?= $this->Form->create($jobLog) ?>
            <fieldset>
                <legend><?= __('Edit Job Log') ?></legend>
                <?php
                    echo $this->Form->control('job_id');
                    echo $this->Form->control('user_id');
                    echo $this->Form->control('action');
                    echo $this->Form->control('comments');
                    echo $this->Form->control('created_at');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
