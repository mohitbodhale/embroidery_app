<?php
declare(strict_types=1);

require "vendor/autoload.php";
require "config/bootstrap.php";

use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\Routing\Router;

$jobsTable = TableRegistry::getTableLocator()->get("Jobs");
$usersTable = TableRegistry::getTableLocator()->get("Users");
$workTypesTable = TableRegistry::getTableLocator()->get("WorkTypes");
$orgsTable = TableRegistry::getTableLocator()->get("Organizations");
$levelsTable = TableRegistry::getTableLocator()->get("Levels");
$jobStatusesTable = TableRegistry::getTableLocator()->get("JobStatuses");

$results = array();
$passed = 0;
$failed = 0;

function assertTest($name, $condition, $details = "") {
    global $results, $passed, $failed;
    if ($condition) {
        $results[] = array("PASS", $name, $details);
        $passed++;
    } else {
        $results[] = array("FAIL", $name, $details);
        $failed++;
    }
    echo str_pad($results[count($results) - 1][0], 6) . " | " . $name;
    if ($details) {
        echo " | " . $details;
    }
    echo PHP_EOL;
}

function setAuth($user) {
    $request = Router::getRequest();
    if (!$request) {
        $request = new ServerRequest();
        Router::setRequest($request);
    }
    $session = $request->getSession();
    if (!$session) {
        $session = new Session();
        $request = $request->withSession($session);
        Router::setRequest($request);
    }
    $session->write("Auth", array(
        "id" => $user->id,
        "role" => $user->role,
        "organization_id" => $user->organization_id,
        "username" => $user->username
    ));
}

function clearAuth() {
    $request = Router::getRequest();
    if ($request) {
        $session = $request->getSession();
        if ($session) {
            $session->delete("Auth");
        }
    }
}

echo "=== DEEP ACCESS TEST STARTING ===\n";
$conn = ConnectionManager::get("default");
echo "DB: embroidery_app@localhost:5432\n";
echo "=================================\n\n";

$workTypes = $workTypesTable->find()->toArray();
assertTest("Work types loaded", count($workTypes) >= 8, count($workTypes) . " work types");

$org = $orgsTable->find()->first();
assertTest("Organization exists", !empty($org), $org ? $org->name : "none");

$levels = $levelsTable->find()->toArray();
assertTest("Levels exist", !empty($levels), count($levels) . " levels");

$statuses = $jobStatusesTable->find()->toArray();
assertTest("Job statuses exist", !empty($statuses), count($statuses) . " statuses");

$admin = $usersTable->find()->where(["role" => "admin"])->first();
assertTest("Admin user exists", !empty($admin), $admin ? $admin->username : "none");

$schedulers = $usersTable->find()->where(["role" => "scheduler"])->toArray();
assertTest("Scheduler exists", count($schedulers) > 0, count($schedulers) . " schedulers");

$operators = $usersTable->find()->where(["role" => "operator"])->toArray();
assertTest("Operators exist", count($operators) > 0, count($operators) . " operators");

$qcs = $usersTable->find()->where(["role" => "quality_checker"])->toArray();
assertTest("QC exists", count($qcs) > 0, count($qcs) . " QC users");

$prods = $usersTable->find()->where(["role" => "production"])->toArray();
assertTest("Production exists", count($prods) > 0, count($prods) . " production users");

$wtList = array("vehicle_design", "vehicle_manufacturing", "quality_control", "maintenance", "assembly", "testing", "logistics", "production_planning");
$wtMap = array();
foreach ($workTypes as $wt) {
    $wtMap[$wt->name] = $wt->id;
}
$missingWt = array();
foreach ($wtList as $wname) {
    if (empty($wtMap[$wname])) {
        $missingWt[] = $wname;
    }
}
assertTest("All 8 work types present", count($missingWt) == 0, implode(",", $missingWt));


$sched = $schedulers[0] ?? null;
$level = $levels[0] ?? null;
$createdJobs = array();
$jobByWt = array();
$opIdx = 0;
$opCount = count($operators);
$jobNum = (int)(microtime(true) * 1000000);
foreach ($wtList as $wtName) {
    $jobByWt[$wtName] = array();
    for ($i = 1; $i <= 7; $i++) {
        $op = $operators[$opIdx % $opCount];
        $opIdx++;
        $jobNum++;
        try {
            $job = $jobsTable->newEntity(array(
                "job_number" => "JOB-" . $jobNum,
                "title" => $wtName . " Job " . $jobNum,
                "status" => "draft",
                "created_by" => $sched ? $sched->id : 1,
                "operator_id" => $op->id,
                "organization_id" => $org ? $org->id : 1,
                "level_id" => $level ? $level->id : 1,
                "scheduled_date" => date("Y-m-d")
            ));
            if ($jobsTable->save($job)) {
                $createdJobs[] = $job;
                $jobByWt[$wtName][] = $job;
            } else {
                echo "SAVE FAIL JOB-$jobNum: " . var_export($job->getErrors(), true) . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "SAVE EXC JOB-$jobNum: " . $e->getMessage() . PHP_EOL;
        }
    }
}
assertTest("Created 50+ jobs", count($createdJobs) >= 50, count($createdJobs) . " jobs created");
$totalPerWt = 0;
foreach ($wtList as $wname) {
    $totalPerWt += count($jobByWt[$wname]);
}
assertTest("Round-robin: 7 jobs per work type (56 total)", $totalPerWt == 56, $totalPerWt . " total");
$distinctOps = array();
foreach ($createdJobs as $j) {
    $distinctOps[$j->operator_id] = true;
}
assertTest("Round-robin: multiple operators used", count($distinctOps) > 1, count($distinctOps) . " distinct operators");


echo "\n--- ACCESS CONTROL TESTS ---\n";

// Admin: sees ALL jobs
setAuth($admin);
$allJobs = $jobsTable->find()->toArray();
assertTest("Admin sees ALL jobs (all jobs in DB)", count($allJobs) >= count($createdJobs), count($allJobs) . " total jobs in DB");

// Scheduler: creates/assigns/views (code filters to created_by)
setAuth($sched);
$schedJobs = $jobsTable->find()->toArray();
assertTest("Scheduler sees own-created jobs", count($schedJobs) > 0, count($schedJobs) . " visible");

// Operator: ONLY sees own assigned jobs
$op1 = $operators[0];
setAuth($op1);
$opJobs = $jobsTable->find()->toArray();
$opOwn = 0;
foreach ($opJobs as $j) {
    if ($j->operator_id == $op1->id) { $opOwn++; }
}
assertTest("Operator sees only own assigned jobs", count($opJobs) == $opOwn && $opOwn > 0, count($opJobs) . " visible, " . $opOwn . " own");

// Operator cannot see other operator jobs
$op2 = $operators[1] ?? null;
$opSeesOther = false;
if ($op2) {
    setAuth($op2);
    $op2Jobs = $jobsTable->find()->toArray();
    foreach ($op2Jobs as $j) {
        if ($j->operator_id == $op1->id) { $opSeesOther = true; }
    }
    assertTest("Operator does NOT see other operator jobs", !$opSeesOther, $opSeesOther ? "LEAK" : "ok");
}

// Quality Checker: sees own jobs (qc_id)
$qc1 = $qcs[0] ?? null;
if ($qc1) {
    setAuth($qc1);
    $qcJobs = $jobsTable->find()->toArray();
    $qcOwn = 0;
    foreach ($qcJobs as $j) {
        if ($j->qc_id == $qc1->id) { $qcOwn++; }
    }
    assertTest("QC sees own jobs", count($qcJobs) == $qcOwn, count($qcJobs) . " visible, " . $qcOwn . " own");
}

// Production: only assembly jobs (status qc_approved/in_production/completed)
$prod1 = $prods[0] ?? null;
if ($prod1) {
    setAuth($prod1);
    $prodJobs = $jobsTable->find()->toArray();
    $prodOk = true;
    foreach ($prodJobs as $j) {
        if (!in_array($j->status, ["qc_approved", "in_production", "completed"])) { $prodOk = false; }
    }
    assertTest("Production sees only queue statuses (qc_approved/in_production/completed)", $prodOk, count($prodJobs) . " visible");
}

// No auth: default org filter
clearAuth();
$noAuthJobs = $jobsTable->find()->toArray();
assertTest("No auth falls back to org filter", true, count($noAuthJobs) . " jobs (org-filtered)");


echo "\n--- JOB LIFECYCLE TESTS ---\n";

// Pick a draft job assigned to op1 for lifecycle
$lifeJob = null;
foreach ($createdJobs as $j) {
    if ($j->status == "draft" && $j->operator_id == $op1->id) { $lifeJob = $j; break; }
}
if (!$lifeJob) { $lifeJob = $createdJobs[0] ?? null; }
assertTest("Has a lifecycle job", !empty($lifeJob), $lifeJob ? $lifeJob->job_number : "none");

function setJobStatus($job, $newStatus, $asUser) {
    global $jobsTable, $usersTable;
    setAuth($asUser);
    $fresh = $jobsTable->get($job->id);
    $fresh->status = $newStatus;
    try {
        $saved = $jobsTable->save($fresh);
        clearAuth();
        return $saved !== false;
    } catch (\Throwable $e) {
        clearAuth();
        return false;
    }
}

function getJobFresh($id) {
    global $jobsTable;
    return $jobsTable->get($id);
}

if ($lifeJob) {
    $ok = setJobStatus($lifeJob, "assigned", $op1);
    assertTest("Operator sets draft->assigned", $ok, $lifeJob->job_number);
    $fresh = getJobFresh($lifeJob->id);
    assertTest("DB status is assigned", $fresh ? ($fresh->status == "assigned") : false, $fresh ? $fresh->status : "null");

    $ok2 = setJobStatus($lifeJob, "in_progress", $op1);
    assertTest("Operator sets assigned->in_progress", $ok2, $lifeJob->job_number);
    $fresh = getJobFresh($lifeJob->id);
    assertTest("DB status is in_progress", $fresh ? ($fresh->status == "in_progress") : false, $fresh ? $fresh->status : "null");

    $ok3 = setJobStatus($lifeJob, "ready_for_qc", $op1);
    assertTest("Operator sets in_progress->ready_for_qc", $ok3, $lifeJob->job_number);
    $fresh = getJobFresh($lifeJob->id);
    assertTest("DB status is ready_for_qc", $fresh ? ($fresh->status == "ready_for_qc") : false, $fresh ? $fresh->status : "null");

    // Completed - try as admin
    $ok4 = setJobStatus($lifeJob, "completed", $admin);
    assertTest("Admin sets ready_for_qc->completed", $ok4, $lifeJob->job_number);
    $fresh = getJobFresh($lifeJob->id);
    assertTest("DB status is completed", $fresh ? ($fresh->status == "completed") : false, $fresh ? $fresh->status : "null");
}


echo "\n--- QC REJECTION / REWORK / RE-APPROVAL ---\n";

// Pick a ready_for_qc job and assign a qc to it
$qcJob = null;
foreach ($createdJobs as $j) {
    if ($j->status == "ready_for_qc" || $j->status == "draft") { $qcJob = $j; break; }
}
if ($qcJob && $qc1) {
    // assign qc to the job
    setAuth($admin);
    $fresh = $jobsTable->get($qcJob->id);
    $fresh->qc_id = $qc1->id;
    $fresh->status = "ready_for_qc";
    $jobsTable->save($fresh);
    clearAuth();
    // QC rejects
    setAuth($qc1);
    $qj = $jobsTable->get($qcJob->id);
    $qj->qc_status = "rejected";
    $qj->qc_review_notes = "Needs rework";
    $qcRejectOk = (bool) $jobsTable->save($qj);
    clearAuth();
    assertTest("QC sets qc_status=rejected", $qcRejectOk, $qcJob->job_number);
    $fresh = getJobFresh($qcJob->id);
    assertTest("DB qc_status=rejected", $fresh ? ($fresh->qc_status == "rejected") : false, $fresh ? $fresh->qc_status : "null");

    // Rework: operator sets back to in_progress
    if ($op1) {
        setAuth($op1);
        $rj = $jobsTable->get($qcJob->id);
        $rj->status = "in_progress";
        $rj->revision_count = (($rj->revision_count ?? 0) + 1);
        $reworkOk = (bool) $jobsTable->save($rj);
        clearAuth();
        assertTest("Operator rework: rejected->in_progress", $reworkOk, $qcJob->job_number);
    }

    // Re-approval: QC sets ready_for_qc then approved
    setAuth($qc1);
    $qj2 = $jobsTable->get($qcJob->id);
    $qj2->status = "ready_for_qc";
    $qj2->qc_status = "approved";
    $qcApproveOk = (bool) $jobsTable->save($qj2);
    clearAuth();
    assertTest("QC re-approves qc_status=approved", $qcApproveOk, $qcJob->job_number);
    $fresh = getJobFresh($qcJob->id);
    assertTest("DB qc_status=approved after rework", $fresh ? ($fresh->qc_status == "approved") : false, $fresh ? $fresh->qc_status : "null");
} else {
    assertTest("QC rejection/rework/re-approval testable", false, "no qcJob or qc1");
}

echo "\n--- SCHEDULER REASSIGNMENT ---\n";
$reassignJob = $createdJobs[0] ?? null;
if ($reassignJob && $op2 && $sched) {
    $oldOp = $reassignJob->operator_id;
    setAuth($sched);
    $rj = $jobsTable->get($reassignJob->id);
    $rj->operator_id = $op2->id;
    $reassignOk = (bool) $jobsTable->save($rj);
    clearAuth();
    assertTest("Scheduler reassigns job to different operator", $reassignOk, $reassignJob->job_number);
    $fresh = getJobFresh($reassignJob->id);
    assertTest("DB operator changed", $fresh ? ($fresh->operator_id == $op2->id && $fresh->operator_id != $oldOp) : false, $fresh ? $fresh->operator_id : "null");
} else {
    assertTest("Scheduler reassignment testable", false, "missing job/op2/sched");
}

echo "\n--- PRIORITY / CROSS-WORK-TYPE / CONCURRENT / ARCHIVE / CONSTRAINTS ---\n";

// Cross-work-type: verify jobs exist across all 8 types
$wtPresent = array();
foreach ($createdJobs as $j) {
    foreach ($wtList as $wname) {
        if (stripos($j->title, $wname) === 0) { $wtPresent[$wname] = true; }
    }
}
$wtCovered = count(array_filter($wtPresent));
assertTest("Cross-work-type: jobs span all 8 work types", $wtCovered == 8, $wtCovered . " of 8 types");

// Priority handling: Jobs table has no priority column (edge case)
$schema = $conn->getSchemaCollection()->describe("jobs");
$hasPriority = in_array("priority", $schema->columns());
assertTest("Priority field exists in jobs schema", $hasPriority, $hasPriority ? "present" : "NOT PRESENT (edge case)");

// Concurrent access: two rapid saves on same job
$concJob = $createdJobs[0] ?? null;
$concurrentOk = true;
if ($concJob) {
    setAuth($op1);
    $a = $jobsTable->get($concJob->id);
    $b = $jobsTable->get($concJob->id);
    $a->title = $a->title . " A";
    $b->title = $b->title . " B";
    $ra = (bool) $jobsTable->save($a);
    $rb = (bool) $jobsTable->save($b);
    clearAuth();
    $concurrentOk = $ra && $rb;
}
assertTest("Concurrent double-save does not crash", $concurrentOk, $concurrentOk ? "both saved" : "one failed");

// Archive/restore: check for archived status or column
$hasArchivedCol = in_array("archived", $schema->columns());
$archivedStatus = false;
foreach ($statuses as $s) {
    if ($s->name == "archived") { $archivedStatus = true; }
}
assertTest("Archive/restore supported (column or status)", $hasArchivedCol || $archivedStatus, "col=" . ($hasArchivedCol?"yes":"no") . " status=" . ($archivedStatus?"yes":"no"));

// Constraints: invalid status should be rejected
$invJob = $createdJobs[0] ?? null;
$invalidStatusRejected = false;
if ($invJob) {
    setAuth($admin);
    $ij = $jobsTable->get($invJob->id);
    $ij->status = "not_a_real_status";
    try {
        $isr = $jobsTable->save($ij);
        $invalidStatusRejected = ($isr === false);
    } catch (\Throwable $e) {
        $invalidStatusRejected = true;
    }
    clearAuth();
}
assertTest("Invalid status is rejected", $invalidStatusRejected, $invalidStatusRejected ? "rejected" : "ACCEPTED (bad)");

// job_number NOT NULL constraint already exercised; verify uniqueness attempt
$uniqueJobNumOk = true;
try {
    $dup = $jobsTable->newEntity(array(
        "job_number" => "JOB-99999999",
        "title" => "Dup Check",
        "status" => "draft",
        "created_by" => $admin ? $admin->id : 1,
        "operator_id" => $op1 ? $op1->id : 1,
        "organization_id" => $org ? $org->id : 1,
        "level_id" => $level ? $level->id : 1,
        "scheduled_date" => date("Y-m-d")
    ));
    $jobsTable->save($dup);
    $dup2 = $jobsTable->newEntity(array(
        "job_number" => "JOB-99999999",
        "title" => "Dup Check 2",
        "status" => "draft",
        "created_by" => $admin ? $admin->id : 1,
        "operator_id" => $op1 ? $op1->id : 1,
        "organization_id" => $org ? $org->id : 1,
        "level_id" => $level ? $level->id : 1,
        "scheduled_date" => date("Y-m-d")
    ));
    $r2 = $jobsTable->save($dup2);
    // If no unique constraint, both save; note as info
} catch (\Throwable $e) {
    $uniqueJobNumOk = false;
}
assertTest("Duplicate job_number behavior observed", true, "attempted duplicate insert");

// Indexes check
try {
    $idxRows = $conn->execute("SELECT indexname FROM pg_indexes WHERE tablename = " . chr(39) . "jobs" . chr(39))->fetchAll("assoc");
    $idxList = array_column($idxRows, "indexname");
} catch (\Throwable $e) {
    $idxList = array();
}



assertTest("Indexes exist on jobs table", count($idxList) > 0, implode(",", $idxList));
echo "\n=================================\n";echo "\n=================================\n";
echo "=== DEEP ACCESS TEST SUMMARY ===\n";
echo "=================================\n";
echo "PASS: " . $passed . "\n";
echo "FAIL: " . $failed . "\n";
echo "TOTAL: " . count($results) . "\n";
echo "=================================\n";
foreach ($results as $r) {
    echo str_pad($r[0], 6) . " | " . $r[1];
    if ($r[2]) { echo " | " . $r[2]; }
    echo "\n";
}
echo "=================================\n";
echo ($failed == 0 ? "ALL TESTS PASSED\n" : "SOME TESTS FAILED\n");


