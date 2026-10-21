<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$roles = \Cake\ORM\TableRegistry::getTableLocator()->get('Roles');
$list = $roles->find()->all();
foreach ($list as $r) {
    echo $r->id . ': ' . $r->name . ' - ' . $r->label . ' (active: ' . ($r->is_active ? 'yes' : 'no') . ')' . PHP_EOL;
}