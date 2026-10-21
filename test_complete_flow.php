<?php
// Test the complete flow: login -> create jobs -> assign to operators
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app_test', 'postgres', 'trust');
    echo "=== Complete Flow Test ===\n\n";
    
    // 1. Test login for all roles
    $users = [
        'admin@stitchcraft.com' => 'admin',
        'scheduler@stitchcraft.com' => 'scheduler',
        'operator@stitchcraft.com' => 'operator',
        'qc1@stitchcraft.com' => 'quality_checker',
        'production@stitchcraft.com' => 'production',
    ];
    
    echo "1. Login Tests:\n";
    foreach ($users as $email => $expectedRole) {
        $stmt = $pdo->prepare("SELECT id, name, email, role, password, work_type FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify('Password123!', $user['password'])) {
            echo "  ✓ {$user['name']} ({$user['email']}) - role: {$user['role']}, work_type: {$user['work_type']}\n";
        } else {
            echo "  ✗ {$email} - LOGIN FAILED\n";
        }
    }
    
    // 2. Create jobs for different work types by scheduler
    echo "\n2. Creating jobs by Scheduler:\n";
    $workTypes = ['vehicle_design', 'vehicle_manufacturing', 'quality_control', 'maintenance', 'assembly', 'testing', 'logistics', 'production_planning'];
    
    foreach ($workTypes as $workType) {
        $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'draft', ?, ?, ?, ?, 1, 0, NOW(), NOW()) RETURNING id");
        $jobNumber = 'JOB-' . strtoupper(substr($workType, 0, 4)) . '-' . rand(1000, 9999);
        $stmt->execute([$jobNumber, "{$workType} Job", "Test job for {$workType}", 1, 1, 1, 1]);
        $jobId = $stmt->fetchColumn();
        echo "  Created job {$jobId} ({$jobNumber}) for work type: {$workType}\n";
    }
    
    // 3. Assign jobs to operators based on work type
    echo "\n3. Assigning jobs to operators:\n";
    $operatorMap = [
        'vehicle_design' => 5, // admin has vehicle_design
        'vehicle_manufacturing' => 2, // operator has vehicle_manufacturing
        'quality_control' => 3, // qc has quality_control
        'maintenance' => 2, // operator
        'assembly' => 4, // production has assembly
        'testing' => 3, // qc
        'logistics' => 1, // scheduler
        'production_planning' => 1, // scheduler
    ];
    
    foreach ($operatorMap as $workType => $operatorId) {
        // Get job for this work type
        $stmt = $pdo->prepare("SELECT id FROM jobs WHERE title LIKE ? AND assigned_to = 1 LIMIT 1");
        $stmt->execute(["%{$workType}%"]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($job) {
            $stmt = $pdo->prepare("UPDATE jobs SET assigned_to = ?, assigned_by = 1, assigned_at = NOW(), status = 'assigned' WHERE id = ?");
            $stmt->execute([$operatorId, $job['id']]);
            echo "  Assigned job {$job['id']} ({$workType}) to operator ID {$operatorId}\n";
        }
    }
    
    // 4. Verify job assignments
    echo "\n4. Job Assignments Summary:\n";
    $stmt = $pdo->query("
        SELECT j.id, j.title, j.status, u.name as operator_name, u.work_type as operator_work_type
        FROM jobs j
        LEFT JOIN users u ON j.assigned_to = u.id
        ORDER BY j.id
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Job {$row['id']}: {$row['title']} - Status: {$row['status']}, Assigned to: {$row['operator_name']} ({$row['operator_work_type']})\n";
    }
    
    // 5. Test operator viewing their jobs
    echo "\n5. Operator Job Views:\n";
    $operatorIds = [2, 3, 4];
    foreach ($operatorIds as $operatorId) {
        $stmt = $pdo->prepare("SELECT name, work_type FROM users WHERE id = ?");
        $stmt->execute([$operatorId]);
        $operator = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT id, title, status FROM jobs WHERE assigned_to = ?");
        $stmt->execute([$operatorId]);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "  {$operator['name']} (work_type: {$operator['work_type']}):\n";
        if ($jobs) {
            foreach ($jobs as $job) {
                echo "    - Job {$job['id']}: {$job['title']} [{$job['status']}]\n";
            }
        } else {
            echo "    (no jobs assigned)\n";
        }
    }
    
    // 6. Test QC workflow - operator submits, QC reviews
    echo "\n6. QC Workflow Test:\n";
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE assigned_to = 2 AND status = 'assigned' LIMIT 1");
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job) {
        echo "  Operator (ID 2) working on Job {$job['id']}\n";
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Status updated to 'in_progress'\n";
        
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'ready_for_qc', qc_status = 'pending' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Status updated to 'ready_for_qc'\n";
        
        // QC user reviews
        $stmt = $pdo->prepare("UPDATE jobs SET qc_status = 'approved', qc_review_notes = 'Quality check passed', qc_reviewed_at = NOW(), status = 'completed' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  QC approved - job completed\n";
    }
    
    echo "\n=== Flow Test Complete ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}