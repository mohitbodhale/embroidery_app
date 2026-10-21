<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$orgs = \Cake\ORM\TableRegistry::getTableLocator()->get('Organizations');
$list = $orgs->find()->all();
foreach ($list as $o) {
    echo $o->id . ': ' . $o->name . ' (' . $o->domain_or_slug . ')' . PHP_EOL;
}