<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$levels = \Cake\ORM\TableRegistry::getTableLocator()->get('Levels');
$list = $levels->find()->all();
foreach ($list as $l) {
    echo $l->id . ': ' . $l->name . ' - ' . $l->label . ' - $' . $l->payment_amount . PHP_EOL;
}