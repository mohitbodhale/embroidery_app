<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$wts = \Cake\ORM\TableRegistry::getTableLocator()->get('WorkTypes');
$list = $wts->find()->all();
foreach ($list as $w) {
    echo $w->id . ': ' . $w->name . ' - ' . $w->label . PHP_EOL;
}