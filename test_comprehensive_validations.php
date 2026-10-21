<?php
// Comprehensive edge case and validation tests
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app', 'postgres', 'trust');
    echo "=== COMPREHENSIVE EDGE CASE & VALIDATION TESTS ===\n\n";
    
    // 1. DATA INTEGRITY - FOREIGN KEY CONSTRAINTS
    echo "1. DATA INTEGRITY TESTS:\n";
    
    try {
        $stmt = $pdo->prepare("UPDATE jobs SET assigned_to = 99999 WHERE id = 2");
        $stmt->execute();
        echo "  ✗ ERROR: Should have failed - assigned to non-existent user\n";
    } catch (Exception $e) {
        echo "  ✓ Correctly rejected assignment to non-existent user\n";
    }
    
    $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'draft', 1, 1, 1, 1, 1, 0, NOW(), NOW()) RETURNING id");
    $stmt->execute(['JOB-INVALID-' . rand(100000, 999999), 'invalid_worktype Job', 'Test']);
    $jobId = $stmt->fetchColumn();
    echo "  ✓ Job with arbitrary title created (no FK constraint on title): {$jobId}\n";
    
    // 2. SECURITY - PASSWORD HASH VALIDATION
    echo "\n2. SECURITY VALIDATIONS:\n";
    $stmt = $pdo->query("SELECT email, password FROM users WHERE role IN ('admin', 'operator', 'quality_checker', 'scheduler') LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hash = $row['password'];
        $valid = password_verify('Password123!', $hash);
        $info = password_get_info($hash);
        $algo = $info['algoName'] ?? 'unknown';
        $cost = $info['options']['cost'] ?? 'N/A';
        echo "  {$row['email']}: valid={$valid}, algo={$algo}, cost={$cost}\n";
    }
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = 'admin@stitchcraft.com'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    $wrong = password_verify('WrongPassword', $hash);
    echo "  Wrong password test: " . ($wrong ? "✗ FAILED" : "✓ Rejected") . "\n";
    
    // 3. CONCURRENT ACCESS SIMULATION
    echo "\n3. CONCURRENT ACCESS SIMULATION:\n";
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE status = 'assigned' AND assigned_to = 12 LIMIT 1");
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job) {
        $jobId = $job['id'];
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress' WHERE id = ? AND status = 'assigned'");
        $stmt->execute([$jobId]);
        $affected1 = $stmt->rowCount();
        $pdo->commit();
        
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE jobs SET assigned_to = 13, status = 'assigned' WHERE id = ? AND status = 'assigned'");
        $stmt->execute([$jobId]);
        $affected2 = $stmt->rowCount();
        $pdo->commit();
        
        echo "  Operator 12 claimed job: " . ($affected1 ? "✓ Success" : "✗ Failed") . "\n";
        echo "  Operator 13 tried to claim: " . ($affected2 ? "✗ Should fail" : "✓ Correctly rejected") . "\n";
    }
    
    // 4. JOB STATUS TRANSITION VALIDATION
    echo "\n4. STATUS TRANSITION TESTS:\n";
    $validTransitions = [
        'draft' => ['assigned'],
        'assigned' => ['in_progress', 'draft'],
        'in_progress' => ['ready_for_qc', 'assigned'],
        'ready_for_qc' => ['completed', 'in_progress'],
        'completed' => [],
    ];
    
    foreach ($validTransitions as $from => $toStates) {
        $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, ?, 1, 1, 1, 1, 1, 0, NOW(), NOW()) RETURNING id");
        $stmt->execute(["JOB-TRANS-{$from}-" . rand(10000, 99999), "Test {$from}", "Status transition test", $from]);
        $jobId = $stmt->fetchColumn();
        
        foreach ($toStates as $to) {
            $stmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ?");
            $stmt->execute([$to, $jobId]);
            echo "  {$from} → {$to}: ✓ Allowed\n";
        }
    }
    
    // 5. QC STATUS VALIDATION
    echo "\n5. QC STATUS VALIDATION:\n";
    $qcStatuses = ['pending', 'approved', 'rejected'];
    foreach ($qcStatuses as $status) {
        $stmt = $pdo->prepare("UPDATE jobs SET qc_status = ? WHERE id = (SELECT id FROM jobs WHERE job_number LIKE 'JOB-TRANS%' AND qc_status IS NULL ORDER BY id LIMIT 1)");
        $stmt->execute([$status]);
        echo "  qc_status = '{$status}': ✓ Accepted\n";
    }
    
    // 6. SOFT DELETE / ARCHIVE TEST
    echo "\n6. ARCHIVE/DELETE SCENARIOS:\n";
    $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'completed', 1, 1, 1, 1, 1, 0, NOW(), NOW()) RETURNING id");
    $stmt->execute(['JOB-ARCHIVE-' . rand(10000, 99999), 'Archive Test', 'Test archive']);
    $jobId = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("UPDATE jobs SET status = 'archived' WHERE id = ?");
    $stmt->execute([$jobId]);
    echo "  Soft delete (status=archived): ✓\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE status != 'archived' AND job_number LIKE 'JOB-ARCHIVE%'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "  Archived job excluded from active: " . ($count == 0 ? "✓" : "✗") . "\n";
    
    // 7. ORGANIZATION ISOLATION TEST
    echo "\n7. ORGANIZATION ISOLATION:\n";
    $stmt = $pdo->query("SELECT DISTINCT organization_id FROM jobs WHERE organization_id IS NOT NULL LIMIT 5");
    $orgs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  Organizations in jobs: " . implode(', ', $orgs) . "\n";
    
    $stmt = $pdo->query("SELECT DISTINCT organization_id FROM users WHERE organization_id IS NOT NULL LIMIT 5");
    $orgs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  Organizations in users: " . implode(', ', $orgs) . "\n";
    
    // 8. WORK TYPE CONSISTENCY
    echo "\n8. WORK TYPE CONSISTENCY:\n";
    $stmt = $pdo->query("
        SELECT u.work_type, COUNT(*) as count
        FROM users u
        LEFT JOIN work_types wt ON u.work_type = wt.name
        WHERE u.work_type IS NOT NULL AND u.work_type != ''
        GROUP BY u.work_type
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt2 = $pdo->prepare("SELECT name FROM work_types WHERE name = ?");
        $stmt2->execute([$row['work_type']]);
        $exists = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "  {$row['work_type']}: {$row['count']} users - " . ($exists ? "✓ Valid" : "✗ INVALID") . "\n";
    }
    
    // 9. JOB NUMBER UNIQUENESS
    echo "\n9. JOB NUMBER UNIQUENESS:\n";
    try {
        $randNum = rand(100000, 999999);
        $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'draft', 1, 1, 1, 1, 1, 0, NOW(), NOW())");
        $stmt->execute(["JOB-DUPLICATE-{$randNum}", 'Test', 'Test']);
        $stmt->execute(["JOB-DUPLICATE-{$randNum}", 'Test2', 'Test2']);
        echo "  ✗ Duplicate job_number allowed (should have unique constraint)\n";
    } catch (Exception $e) {
        echo "  ✓ Duplicate job_number rejected\n";
    }
    
    // 10. PERFORMANCE - BULK OPERATIONS
    echo "\n10. BULK OPERATION PERFORMANCE:\n";
    $start = microtime(true);
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, assigned_to, assigned_by, level_id, level_payment, created_at, updated_at) VALUES (?, ?, ?, 'draft', 1, 1, 1, 1, 1, 0, NOW(), NOW())");
    for ($i = 1; $i <= 100; $i++) {
        $stmt->execute(["JOB-BULK-" . rand(1000000, 9999999), "Bulk Job {$i}", "Performance test"]);
    }
    $pdo->commit();
    $time = microtime(true) - $start;
    echo "  100 job inserts: " . round($time * 1000, 2) . "ms\n";
    
    // 11. AUDIT TRAIL - TIMESTAMPS
    echo "\n11. AUDIT TRAIL VALIDATION:\n";
    $stmt = $pdo->prepare("SELECT created_at, updated_at, assigned_at, qc_reviewed_at FROM jobs WHERE id = 2");
    $stmt->execute();
    $times = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Job 2 timestamps:\n";
    foreach ($times as $key => $val) {
        echo "    {$key}: " . ($val ?? 'NULL') . "\n";
    }
    
    // 12. ROLE-BASED ACCESS SIMULATION
    echo "\n12. ROLE-BASED ACCESS CHECKS:\n";
    $roles = ['admin' => 'Full access', 'scheduler' => 'Create/Assign jobs', 'operator' => 'View/Work on assigned', 'quality_checker' => 'Review QC', 'production' => 'Assembly only'];
    foreach ($roles as $role => $access) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        $count = $stmt->fetchColumn();
        echo "  {$role} ({$count} users): {$access}\n";
    }
    
    // 13. EMAIL UNIQUENESS
    echo "\n13. EMAIL UNIQUENESS:\n";
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, organization_id, work_type, created_at) VALUES (?, ?, ?, 'operator', 1, 'vehicle_design', NOW())");
        $hash = password_hash('Password123!', PASSWORD_BCRYPT);
        $stmt->execute(['Test User', 'admin@stitchcraft.com', $hash]);
        echo "  ✗ Duplicate email allowed\n";
    } catch (Exception $e) {
        echo "  ✓ Duplicate email rejected\n";
    }
    
    // 14. WORK TYPE ENUM CHECK
    echo "\n14. WORK TYPE TABLE STRUCTURE:\n";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'work_types' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['column_name']}: {$row['data_type']}, nullable={$row['is_nullable']}, default={$row['column_default']}\n";
    }
    
    // 15. JOB TABLE STRUCTURE
    echo "\n15. JOB TABLE STRUCTURE:\n";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'jobs' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['column_name']}: {$row['data_type']}, nullable={$row['is_nullable']}\n";
    }
    
    // 16. INDEX CHECK
    echo "\n16. DATABASE INDEXES:\n";
    $stmt = $pdo->query("
        SELECT indexname, tablename, indexdef 
        FROM pg_indexes 
        WHERE tablename IN ('users', 'jobs', 'work_types') 
        ORDER BY tablename, indexname
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['tablename']}.{$row['indexname']}: {$row['indexdef']}\n";
    }
    
    // 17. FOREIGN KEY CONSTRAINTS
    echo "\n17. FOREIGN KEY CONSTRAINTS:\n";
    $stmt = $pdo->query("
        SELECT
            tc.table_name, 
            kcu.column_name, 
            ccu.table_name AS foreign_table_name,
            ccu.column_name AS foreign_column_name 
        FROM 
            information_schema.table_constraints AS tc 
            JOIN information_schema.key_column_usage AS kcu
              ON tc.constraint_name = kcu.constraint_name
              AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
              ON ccu.constraint_name = tc.constraint_name
              AND ccu.table_schema = tc.table_schema
        WHERE tc.constraint_type = 'FOREIGN KEY' 
        AND tc.table_name IN ('users', 'jobs', 'work_types')
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['table_name']}.{$row['column_name']} → {$row['foreign_table_name']}.{$row['foreign_column_name']}\n";
    }
    
    // 18. REVISION TRACKING
    echo "\n18. REVISION TRACKING:\n";
    $stmt = $pdo->prepare("SELECT id, title, revision_count FROM jobs WHERE revision_count > 0 LIMIT 5");
    $stmt->execute();
    $revs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($revs) {
        foreach ($revs as $rev) {
            echo "  Job {$rev['id']}: {$rev['title']} - revisions: {$rev['revision_count']}\n";
        }
    } else {
        echo "  No jobs with revisions yet\n";
    }
    
    echo "\n=== COMPREHENSIVE VALIDATIONS COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}