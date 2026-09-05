<?php
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap_cli.php';

use Cake\Core\Configure;
Configure::write('debug', true);

$Users = new App\Model\Table\UsersTable();
$user = $Users->find()->where(['email' => 'admin@stitchcraft.com'])->first();
echo 'User found: ' . ($user ? 'YES' : 'NO') . PHP_EOL;
if ($user) {
    echo 'ID: ' . $user->id . PHP_EOL;
    echo 'Name: ' . $user->name . PHP_EOL;
    echo 'Role: ' . $user->role . PHP_EOL;
    echo 'Password hash length: ' . strlen($user->password) . PHP_EOL;
    echo 'Password verify: ' . (password_verify('password123', $user->password) ? 'OK' : 'FAILED') . PHP_EOL;
} else {
    echo "No user with that email\n";
    // List all users
    $all = $Users->find()->all();
    echo "Total users: " . $all->count() . "\n";
    foreach ($all as $u) {
        echo "  - ID: {$u->id}, Email: {$u->email}, Role: {$u->role}\n";
    }
}
