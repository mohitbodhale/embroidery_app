<?php
require 'vendor/autoload.php';
require 'config/bootstrap_cli.php';
require 'config/bootstrap.php';

echo "PHP version: " . PHP_VERSION . "\n";

// Test if loading JobAttachment entity triggers the error
try {
    $entity = new \App\Model\Entity\JobAttachment();
    echo "JobAttachment entity created: OK\n";
    echo "Accessible properties: "; var_export($entity->getAccessible()); echo "\n";
} catch (\Throwable $e) {
    echo "JobAttachment ERROR: " . $e->getMessage() . "\n";
}

// Test Job entity
try {
    $entity = new \App\Model\Entity\Job();
    echo "Job entity created: OK\n";
} catch (\Throwable $e) {
    echo "Job ERROR: " . $e->getMessage() . "\n";
}

// Test User entity
try {
    $entity = new \App\Model\Entity\User();
    echo "User entity created: OK\n";
} catch (\Throwable $e) {
    echo "User ERROR: " . $e->getMessage() . "\n";
}
