<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$counters = \Cake\ORM\TableRegistry::getTableLocator()->get('JobCounters');
$list = $counters->find()->all();
foreach ($list as $c) {
    echo "Org: " . $c->organization_id . " | Prefix: " . $c->prefix . " | Next: " . $c->next_number . " | Reset: " . ($c->reset_frequency ?? 'none') . PHP_EOL;
}