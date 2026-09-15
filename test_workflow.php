<?php
require 'vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Locator\TableLocator;
use Cake\Database\Driver\Postgres;
use App\Model\Table\UsersTable;
use App\Model\Table\JobsTable;
use App\Model\Table\WorkTypesTable;
use App\Model\Table\UserDetailsTable;
use App\Policy\JobPolicy;

// Set up database connection
ConnectionManager::setConfig('default', [
    'className' => 'Cake\Database\Connection',
    'driver' => Postgres::class,
    'host' => 'localhost',
    'username' => 'postgres',
    'password' => 'root',
    'database' => 'embroidery_scheduler',
    'schema' => 'public',
    'port' => '5432',
]);

FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(true));

$usersTable = FactoryLocator::get('Table')->get('Users');
$jobsTable = FactoryLocator::get('Table')->get('Jobs');
$workTypesTable = FactoryLocator::get('Table')->get('WorkTypes');
$userDetailsTable = FactoryLocator::get('Table')->get('UserDetails');

echo "=== Users ===\n";
$users = $usersTable->find()->all();
foreach ($users as $u) {
    $details = $userDetailsTable->find()->where(['user_id' => $u->id])->first();
    $wt = 'none';
    if ($details && !empty($details->work_type_id)) {
        $wtEntity = $workTypesTable->find()->where(['id' => $details->work_type_id])->first();
        $wt = $wtEntity ? $wtEntity->name : 'none';
    }
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Role: {$u->role}, WorkType: {$wt}\n";
}

echo "\n=== Jobs ===\n";
$jobs = $jobsTable->find()->all();
foreach ($jobs as $j) {
    $opName = 'none';
    if ($j->operator_id) {
        $op = $usersTable->find()->where(['id' => $j->operator_id])->first();
        $opName = $op ? $op->name : 'none';
    }
    echo "ID: {$j->id}, Job#: {$j->job_number}, Title: {$j->title}, Status: {$j->status}, Operator: {$opName}\n";
}

echo "\n=== Work Types ===\n";
$wts = $workTypesTable->find()->all();
foreach ($wts as $wt) {
    echo "ID: {$wt->id}, Name: {$wt->name}, Label: {$wt->label}\n";
}

// Test JobPolicy
echo "\n=== Policy Tests ===\n";
$policy = new JobPolicy();

$operatorUser = $usersTable->find()->where(['email' => 'operator.digitizing@stitchcraft.com'])->first();
$schedulerUser = $usersTable->find()->where(['email' => 'scheduler@stitchcraft.com'])->first();
$qcUser = $usersTable->find()->where(['email' => 'qc@stitchcraft.com'])->first();
$productionUser = $usersTable->find()->where(['email' => 'production@stitchcraft.com'])->first();
$adminUser = $usersTable->find()->where(['email' => 'admin@stitchcraft.com'])->first();

$job = $jobsTable->find()->where(['job_number' => 'JOB-001'])->first();

echo "\nJob: {$job->job_number} - {$job->title} - Status: {$job->status}\n";
echo "Operator ID: {$job->operator_id}, QC ID: {$job->qc_id}, Created by: {$job->created_by}\n";

echo "\n--- canView ---\n";
echo "Admin: " . ($policy->canView($adminUser, $job) ? 'YES' : 'NO') . "\n";
echo "Scheduler: " . ($policy->canView($schedulerUser, $job) ? 'YES' : 'NO') . "\n";
echo "QC: " . ($policy->canView($qcUser, $job) ? 'YES' : 'NO') . "\n";
echo "Production: " . ($policy->canView($productionUser, $job) ? 'YES' : 'NO') . "\n";
echo "Digitizing Operator (owner): " . ($policy->canView($operatorUser, $job) ? 'YES' : 'NO') . "\n";

$otherOperator = $usersTable->find()->where(['email' => 'operator.programming@stitchcraft.com'])->first();
echo "Programming Operator (not owner): " . ($policy->canView($otherOperator, $job) ? 'YES' : 'NO') . "\n";

echo "\n--- canEdit ---\n";
echo "Admin: " . ($policy->canEdit($adminUser, $job) ? 'YES' : 'NO') . "\n";
echo "Scheduler: " . ($policy->canEdit($schedulerUser, $job) ? 'YES' : 'NO') . "\n";
echo "Digitizing Operator (owner, in_progress): " . ($policy->canEdit($operatorUser, $job) ? 'YES' : 'NO') . "\n";
echo "Programming Operator (not owner): " . ($policy->canEdit($otherOperator, $job) ? 'YES' : 'NO') . "\n";

echo "\n--- canSubmit ---\n";
echo "Digitizing Operator (owner): " . ($policy->canSubmit($operatorUser, $job) ? 'YES' : 'NO') . "\n";
echo "Programming Operator (not owner): " . ($policy->canSubmit($otherOperator, $job) ? 'YES' : 'NO') . "\n";

echo "\n--- canApprove ---\n";
echo "QC (assigned): " . ($policy->canApprove($qcUser, $job) ? 'YES' : 'NO') . "\n";
echo "Admin: " . ($policy->canApprove($adminUser, $job) ? 'YES' : 'NO') . "\n";

echo "\n--- canProduce ---\n";
echo "Production: " . ($policy->canProduce($productionUser, $job) ? 'YES' : 'NO') . "\n";
echo "Operator: " . ($policy->canProduce($operatorUser, $job) ? 'YES' : 'NO') . "\n";

// Test workflow: submit job
echo "\n=== Workflow Test: Submit Job ===\n";
if ($policy->canSubmit($operatorUser, $job)) {
    echo "Operator CAN submit - changing status to 'ready_for_qc'\n";
    $job->status = 'ready_for_qc';
    $jobsTable->save($job);
    echo "Job status changed to: {$job->status}\n";
} else {
    echo "Operator CANNOT submit\n";
}

// Test workflow: approve job
echo "\n=== Workflow Test: Approve Job ===\n";
$job = $jobsTable->find()->where(['job_number' => 'JOB-001'])->first();
if ($policy->canApprove($qcUser, $job)) {
    echo "QC CAN approve - changing status to 'qc_approved'\n";
    $job->status = 'qc_approved';
    $jobsTable->save($job);
    echo "Job status changed to: {$job->status}\n";
} else {
    echo "QC CANNOT approve\n";
}

// Test workflow: start production
echo "\n=== Workflow Test: Start Production ===\n";
$job = $jobsTable->find()->where(['job_number' => 'JOB-001'])->first();
if ($policy->canProduce($productionUser, $job)) {
    echo "Production CAN start - changing status to 'in_production'\n";
    $job->status = 'in_production';
    $jobsTable->save($job);
    echo "Job status changed to: {$job->status}\n";
} else {
    echo "Production CANNOT start\n";
}

// Test workflow: complete production
echo "\n=== Workflow Test: Complete Production ===\n";
$job = $jobsTable->find()->where(['job_number' => 'JOB-001'])->first();
if ($policy->canProduce($productionUser, $job)) {
    echo "Production CAN complete - changing status to 'completed'\n";
    $job->status = 'completed';
    $jobsTable->save($job);
    echo "Job status changed to: {$job->status}\n";
} else {
    echo "Production CANNOT complete\n";
}

// Test reject workflow
echo "\n=== Workflow Test: Reject Job (from ready_for_qc) ===\n";
$job = $jobsTable->find()->where(['job_number' => 'JOB-001'])->first();
$job->status = 'ready_for_qc';
$jobsTable->save($job);
if ($policy->canApprove($qcUser, $job)) {
    echo "QC CAN reject - changing status to 'qc_rejected'\n";
    $job->status = 'qc_rejected';
    $jobsTable->save($job);
    echo "Job status changed to: {$job->status}\n";
} else {
    echo "QC CANNOT reject\n";
}

// Test operator edit after reject
echo "\n=== Workflow Test: Operator Edit after Reject ===\n";
$job = $jobsTable->find()->where(['job_number' => 'JOB-001'])->first();
echo "Job status: {$job->status}\n";
echo "Digitizing Operator can edit after reject: " . ($policy->canEdit($operatorUser, $job) ? 'YES' : 'NO') . "\n";

echo "\n=== All Tests Complete ===\n";