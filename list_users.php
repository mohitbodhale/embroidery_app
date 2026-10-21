<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$users = \Cake\ORM\TableRegistry::getTableLocator()->get('Users');
$usersList = $users->find()->all();
foreach ($usersList as $user) {
    echo "ID: " . $user->id . " | Email: " . $user->email . " | Role: " . $user->role . " | Org: " . $user->organization_id . PHP_EOL;
}