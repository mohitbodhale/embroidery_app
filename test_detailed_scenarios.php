<?php
// Comprehensive detailed scenario tests
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app', 'postgres', 'trust');
    echo "=== DETAILED SCENARIO TESTS ===\n\n";
    
    // Get all operator IDs by work type
    $stmt = $pdo->query("SELECT id, name, email, role, work_type FROM users WHERE work_type IS NOT NULL AND work_type != '' AND role IN ('operator', 'quality_checker', 'scheduler', 'admin') ORDER BY work_type, name");
    $allOperators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by work type
    $operatorsByWorkType = [];
    foreach ($allOperators as $op) {
        $operatorsByWorkType[$op['work_type']][] = $op;
    }
    
    echo "1. MULTIPLE OPERATORS PER WORK TYPE - LOAD DISTRIBUTION:\n";
    foreach ($operatorsByWorkType as $workType => $operators) {
        echo "  {$workType} ({$operators['count']} operators):\n";
        // Create 3 jobs per work type
        $schedulerId = 1;
        for ($i = 1; $i <= 3; $i++) {
            $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'draft', ?, 1, ?, ?, 1, 0, NOW(), NOW()) RETURNING id");
            $jobNumber = 'JOB-' . strtoupper(substr($workType, 0, 4)) . '-' . rand(10000, 99999);
            // Distribute among operators
            $operatorIndex = ($i - 1) % count($operators);
            $operatorId = $operators[$operatorIndex]['id'];
            $stmt->execute([$jobNumber, "{$workType} Job {$i}", "Test job {$i} for {$workType}", $schedulerId, $operatorId, $schedulerId]);
            $jobId = $stmt->fetchColumn();
            echo "    Job {$jobId} assigned to {$operators[$operatorIndex]['name']}\n";
        }
    }
    
    // 2. QC REJECTION SCENARIO
    echo "\n2. QC REJECTION SCENARIO:\n";
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE assigned_to = 12 AND status = 'assigned' LIMIT 1");
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($job) {
        echo "  QC rejects Job {$job['id']} (vehicle_manufacturing)\n";
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress', qc_status = 'rejected', qc_review_notes = 'Defect found: tolerance exceeded', qc_reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Status: in_progress, QC: rejected\n";
        
        // Operator reworks
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'ready_for_qc', qc_status = 'pending', qc_review_notes = NULL WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Operator reworks, resubmits for QC\n";
        
        // QC approves
        $stmt = $pdo->prepare("UPDATE jobs SET qc_status = 'approved', qc_review_notes = 'Rework acceptable', qc_reviewed_at = NOW(), status = 'completed' WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  QC approves rework, Job completed\n";
    }
    
    // 3. OPERATOR WITH MULTIPLE JOBS - WORKLOAD CHECK
    echo "\n3. OPERATOR WORKLOAD CHECK:\n";
    $stmt = $pdo->query("
        SELECT u.name, u.work_type, u.role,
               COUNT(CASE WHEN j.status = 'assigned' THEN 1 END) as assigned,
               COUNT(CASE WHEN j.status = 'in_progress' THEN 1 END) as in_progress,
               COUNT(CASE WHEN j.status = 'ready_for_qc' THEN 1 END) as ready_for_qc,
               COUNT(CASE WHEN j.status = 'completed' THEN 1 END) as completed
        FROM users u
        LEFT JOIN jobs j ON u.id = j.assigned_to
        WHERE u.role IN ('operator', 'quality_checker') AND u.work_type IS NOT NULL AND u.work_type != ''
        GROUP BY u.id, u.name, u.work_type, u.role
        ORDER BY u.work_type, u.name
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['name']} ({$row['work_type']}): Assigned={$row['assigned']}, In Progress={$row['in_progress']}, Ready for QC={$row['ready_for_qc']}, Completed={$row['completed']}\n";
    }
    
    // 4. JOB PRIORITY HANDLING
    echo "\n4. JOB PRIORITY TEST:\n";
    $schedulerId = 1;
    $priorities = ['high', 'medium', 'low'];
    foreach ($priorities as $priority) {
        $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'assigned', ?, 1, ?, ?, 1, 0, NOW(), NOW()) RETURNING id");
        $jobNumber = 'JOB-PRIO-' . strtoupper($priority) . '-' . rand(10000, 99999);
        $stmt->execute([$jobNumber, "Priority {$priority} Job", "High priority urgent job", $schedulerId, 12, $schedulerId]);
        $jobId = $stmt->fetchColumn();
        echo "  Created priority {$priority} job {$jobId}\n";
    }
    
    // 5. SCHEDULER REASSIGNMENT
    echo "\n5. SCHEDULER REASSIGNMENT:\n";
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE assigned_to = 12 AND status = 'assigned' LIMIT 1");
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($job) {
        echo "  Reassigning Job {$job['id']} from Assembly Tech 1 to Assembly Tech 2\n";
        $stmt = $pdo->prepare("UPDATE jobs SET assigned_to = 13, assigned_by = 1, assigned_at = NOW() WHERE id = ?");
        $stmt->execute([$job['id']]);
        echo "  Reassigned to Assembly Tech 2 (ID: 13)\n";
    }
    
    // 6. CROSS WORK TYPE JOB CREATION
    echo "\n6. CROSS WORK TYPE DEPENDENCY TEST:\n";
    // Create a sequence: design -> manufacturing -> qc -> assembly
    $sequence = ['vehicle_design', 'vehicle_manufacturing', 'quality_control', 'assembly'];
    $prevJobId = null;
    foreach ($sequence as $index => $workType) {
        $operatorId = $operatorsByWorkType[$workType][0]['id'] ?? 1;
        $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, ?, ?, 1, 0, NOW(), NOW()) RETURNING id");
        $jobNumber = 'JOB-SEQ-' . ($index + 1) . '-' . rand(10000, 99999);
        $status = $index === 0 ? 'assigned' : 'draft';
        $stmt->execute([$jobNumber, "Sequence Step {$index}: {$workType}", "Part of production sequence", $status, 1, $operatorId, 1]);
        $jobId = $stmt->fetchColumn();
        echo "  Step {$index}: {$workType} Job {$jobId} (status: {$status})\n";
        $prevJobId = $jobId;
    }
    
    // 7. ADMIN JOB OVERVIEW WITH FILTERS
    echo "\n7. ADMIN FILTERED VIEWS:\n";
    // By status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count FROM jobs 
        WHERE job_number LIKE 'JOB-%' 
        GROUP BY status ORDER BY status
    ");
    echo "  By Status:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    {$row['status']}: {$row['count']}\n";
    }
    
    // By work type (from title)
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
                ELSE 'other'
            END as work_type,
            COUNT(*) as count
        FROM jobs 
        WHERE job_number LIKE 'JOB-%'
        GROUP BY work_type ORDER BY work_type
    ");
    echo "  By Work Type:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    {$row['work_type']}: {$row['count']}\n";
    }
    
    // 8. OPERATOR SPECIFIC VIEWS
    echo "\n8. OPERATOR DETAILED VIEWS:\n";
    foreach (['vehicle_manufacturing', 'quality_control', 'vehicle_design'] as $workType) {
        $operators = $operatorsByWorkType[$workType];
        $operator = $operators[0];
        $stmt = $pdo->prepare("
            SELECT j.id, j.job_number, j.title, j.status, j.qc_status, j.created_at
            FROM jobs j
            WHERE j.assigned_to = ? AND j.job_number LIKE 'JOB-%'
            ORDER BY j.created_at DESC
        ");
        $stmt->execute([$operator['id']]);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "  {$operator['name']} ({$workType}) - {$jobs['count']} jobs:\n";
        foreach ($jobs as $job) {
            echo "    Job {$job['id']}: {$job['job_number']} - {$job['title']} [{$job['status']}]\n";
        }
    }
    
    echo "\n=== DETAILED SCENARIO TESTS COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}