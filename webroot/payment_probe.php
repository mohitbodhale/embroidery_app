<?php
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

use Cake\ORM\TableRegistry;

header('Content-Type: text/plain');

echo 'PHP: ' . PHP_VERSION . ' SAPI: ' . PHP_SAPI . "\n";
echo 'controller mtime: ' . date('Y-m-d H:i:s', filemtime(dirname(__DIR__) . '/src/Controller/JobsController.php')) . "\n";
echo 'template mtime: ' . date('Y-m-d H:i:s', filemtime(dirname(__DIR__) . '/templates/Jobs/edit.php')) . "\n";

$Jobs = TableRegistry::getTableLocator()->get('Jobs');
echo 'paymentFor exists: ' . var_export(method_exists($Jobs, 'paymentFor'), true) . "\n";
$job = $Jobs->get(27, contain: ['Levels']);
echo 'job 27: level_id=' . var_export($job->level_id, true)
    . ' scheduled=' . var_export((string)$job->scheduled_date, true)
    . ' stored=' . var_export($job->level_payment, true) . "\n";
echo 'paymentFor(27): ' . var_export($Jobs->paymentFor($job), true) . "\n";
echo 'opcache enabled: ' . var_export(function_exists('opcache_get_status') && (opcache_get_status(false)['opcache_enabled'] ?? false), true) . "\n";
