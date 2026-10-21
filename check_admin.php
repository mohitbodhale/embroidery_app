<?php
require 'vendor/autoload.php';
$app = require 'config/bootstrap.php';
$users = \Cake\ORM\TableRegistry::getTableLocator()->get('Users');
$admin = $users->find()->where(['email' => 'admin@stitchcraft.com'])->first();
echo "Admin ID: " . $admin->id . PHP_EOL;
echo "Admin Org ID: " . $admin->organization_id . PHP_EOL;
echo "Admin Role: " . $admin->role . PHP_EOL;