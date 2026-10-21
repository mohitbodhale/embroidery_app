<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$rates = \Cake\ORM\TableRegistry::getTableLocator()->get('LevelRates');
$list = $rates->find()->contain('Levels')->all();
foreach ($list as $r) {
    echo "Level: " . ($r->level->label ?? $r->level_id) . " | From: " . ($r->valid_from ? $r->valid_from->format('Y-m-d') : 'null') . " | To: " . ($r->valid_to ? $r->valid_to->format('Y-m-d') : 'null') . " | Amount: $" . $r->payment_amount . PHP_EOL;
}