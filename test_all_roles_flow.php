<?php
// Complete flow test with all roles
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app', 'postgres', 'trust');
    echo "=== COMPLETE FLOW TEST: All Roles ===\n\n";
    
    // 1. LOGIN TESTS FOR ALL ROLES
    echo "1. LOGIN TESTS:\n";
    $users = [
        ['email' => 'admin@stitchcraft.com', 'expected_role' => 'admin', 'expected_work_type' => 'vehicle_design'],
        ['email' => 'scheduler@stitchcraft.com', 'expected_role' => 'scheduler', 'expected_work_type' => 'production_planning'],
        ['email' => 'design1@stitchcraft.com', 'expected_role' => 'operator', 'expected_work_type' => 'vehicle_design'],
        ['email' => 'assembly1@stitchcraft.com', 'expected_role' => 'operator', 'expected_work_type' => 'vehicle_manufacturing'],
        ['email' => 'qc1@stitchcraft.com', 'expected_role' => 'quality_checker', 'expected_work_type' => 'quality_control'],
        ['email' => 'production@stitchcraft.com', 'expected_role' => 'production', 'expected_work_type' => 'assembly'],
        ['email' => 'maint1@stitchcraft.com', 'expected_role' => 'operator', 'expected_work_type' => 'maintenance'],
        ['email' => 'test_eng1@stitchcraft.com', 'expected_role' => 'operator', 'expected_work_type' => 'testing'],
        ['email' => 'logistics_coord@stitchcraft.com', 'expected_role' => 'operator', 'expected_work_type' => 'logistics'],
        ['email' => 'planner1@stitchcraft.com', 'expected_role' => 'scheduler', 'expected_work_type' => 'production_planning'],
    ];
    
    $loggedInUsers = [];
    foreach ($users as $userInfo) {
        $stmt = $pdo->prepare("SELECT id, name, email, role, work_type, password FROM users WHERE email = ?");
        $stmt->execute([$userInfo['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify('Password123!', $user['password'])) {
            echo "  ✓ {$user['name']} ({$user['email']}) - role: {$user['role']}, work_type: {$user['work_type']}\n";
            $loggedInUsers[$userInfo['email']] = $user;
        } else {
            echo "  ✗ {$userInfo['email']} - LOGIN FAILED\n";
        }
    }
    
    // 2. SCHEDULER CREATES JOBS FOR ALL WORK TYPES
    echo "\n2. SCHEDULER CREATES JOBS:\n";
    $schedulerId = $loggedInUsers['scheduler@stitchcraft.com']['id'];
    $workTypes = ['vehicle_design', 'vehicle_manufacturing', 'quality_control', 'maintenance', 'assembly', 'testing', 'logistics', 'production_planning'];
    
    $createdJobs = [];
    foreach ($workTypes as $workType) {
        $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'draft', ?, 1, ?, ?, 1, 0, NOW(), NOW()) RETURNING id");
        $jobNumber = 'JOB-' . strtoupper(substr($workType, 0, 4)) . '-' . rand(1000, 9999);
        $stmt->execute([$jobNumber, "{$workType} Production Job", "Test job for {$workType} phase", $schedulerId, $schedulerId, $schedulerId]);
        $jobId = $stmt->fetchColumn();
        $createdJobs[$workType] = $jobId;
        echo "  Created job {$jobId} ({$jobNumber}) for {$workType}\n";
    }
    
    // 3. ASSIGN JOBS TO OPERATORS BASED ON WORK TYPE
    echo "\n3. ASSIGN JOBS TO OPERATORS:\n";
    $operatorMap = [
        'vehicle_design' => $loggedInUsers['design1@stitchcraft.com']['id'],
        'vehicle_manufacturing' => $loggedInUsers['assembly1@stitchcraft.com']['id'],
        'quality_control' => $loggedInUsers['qc1@stitchcraft.com']['id'],
        'maintenance' => $loggedInUsers['maint1@stitchcraft.com']['id'],
        'assembly' => $loggedInUsers['production@stitchcraft.com']['id'],
        'testing' => $loggedInUsers['test_eng1@stitchcraft.com']['id'],
        'logistics' => $loggedInUsers['logistics_coord@stitchcraft.com']['id'],
        'production_planning' => $loggedInUsers['planner1@stitchcraft.com']['id'],
    ];
    
    foreach ($operatorMap as $workType => $operatorId) {
        $jobId = $createdJobs[$workType];
        $stmt = $pdo->prepare("UPDATE jobs SET assigned_to = ?, assigned_by = ?, assigned_at = NOW(), status = 'assigned' WHERE id = ?");
        $stmt->execute([$operatorId, $schedulerId, $jobId]);
        echo "  Assigned job {$jobId} ({$workType}) to operator ID {$operatorId}\n";
    }
    
    // 4. OPERATORS VIEW THEIR JOBS
    echo "\n4. OPERATORS VIEW THEIR ASSIGNED JOBS:\n";
    foreach ($operatorMap as $workType => $operatorId) {
        $stmt = $pdo->prepare("SELECT name, work_type FROM users WHERE id = ?");
        $stmt->execute([$operatorId]);
        $operator = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT id, title, status FROM jobs WHERE assigned_to = ? AND title LIKE ?");
        $stmt->execute([$operatorId, "%{$workType}%"]);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "  {$operator['name']} (work_type: {$operator['work_type']}):\n";
        if ($jobs) {
            foreach ($jobs as $job) {
                echo "    - Job {$job['id']}: {$job['title']} [{$job['status']}]\n";
            }
        } else {
            echo "    (no jobs found)\n";
        }
    }
    
    // 5. OPERATOR WORKS ON JOB (vehicle_manufacturing example)
    echo "\n5. OPERATOR WORKS ON JOB (vehicle_manufacturing):\n";
    $operatorId = $loggedInUsers['assembly1@stitchcraft.com']['id'];
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE assigned_to = ? AND title LIKE '%vehicle_manufacturing%' LIMIT 1");
    $stmt->execute([$operatorId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job) {
        echo "  Operator {$operatorId} starts Job {$job['id']}\n";
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Status: in_progress\n";
        
        echo "  Operator completes work, submits for QC\n";
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'ready_for_qc', qc_status = 'pending' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Status: ready_for_qc, QC status: pending\n";
    }
    
    // 6. QC REVIEWS JOB
    echo "\n6. QC REVIEWS JOB:\n";
    $qcId = $loggedInUsers['qc1@stitchcraft.com']['id'];
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE status = 'ready_for_qc' AND title LIKE '%vehicle_manufacturing%' LIMIT 1");
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job) {
        echo "  QC ({$qcId}) reviews Job {$job['id']}\n";
        $stmt = $pdo->prepare("UPDATE jobs SET qc_status = 'approved', qc_review_notes = 'Quality check passed - all specs met', qc_reviewed_at = NOW(), status = 'completed' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  QC Status: approved, Job Status: completed\n";
    }
    
    // 7. ADMIN VIEWS ALL JOBS
    echo "\n7. ADMIN VIEWS ALL JOBS:\n";
    $stmt = $pdo->query("
        SELECT j.id, j.title, j.status, j.qc_status, u.name as assignee_name, u.work_type as assignee_work_type
        FROM jobs j
        LEFT JOIN users u ON j.assigned_to = u.id
        WHERE j.title LIKE '%Production Job%'
        ORDER BY j.id
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Job {$row['id']}: {$row['title']}\n";
        echo "    Status: {$row['status']}, QC: {$row['qc_status']}\n";
        echo "    Assigned to: {$row['assignee_name']} ({$row['assignee_work_type']})\n";
    }
    
    // 8. SCHEDULER VIEWS JOB STATISTICS
    echo "\n8. JOB STATISTICS BY WORK TYPE:\n";
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN title LIKE '%vehicle_design%' THEN 'vehicle_design'
                WHEN title LIKE '%vehicle_manufacturing%' THEN 'vehicle_manufacturing'
                WHEN title LIKE '%quality_control%' THEN 'quality_control'
                WHEN title LIKE '%maintenance%' THEN 'maintenance'
                WHEN title LIKE '%assembly%' THEN 'assembly'
                WHEN title LIKE '%testing%' THEN 'testing'
                WHEN title LIKE '%logistics%' THEN 'logistics'
                WHEN title LIKE '%production_planning%' THEN 'production_planning'
            END as work_type,
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
            COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
            COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned
        FROM jobs
        WHERE title LIKE '%Production Job%'
        GROUP BY work_type
        ORDER BY work_type
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['work_type']}: Total={$row['total']}, Completed={$row['completed']}, In Progress={$row['in_progress']}, Assigned={$row['assigned']}\n";
    }
    
    echo "\n=== ALL ROLE FLOW TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}