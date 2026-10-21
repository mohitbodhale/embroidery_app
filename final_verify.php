<?php
// Final verification of embroidery_app database
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app', 'postgres', 'trust');
    echo "=== FINAL VERIFICATION: embroidery_app database ===\n\n";
    
    // 1. Work types
    $stmt = $pdo->query("SELECT name, label FROM work_types ORDER BY sort_order");
    echo "1. WORK TYPES (8):\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['name']} ({$row['label']})\n";
    }
    
    // 2. All users with work types
    echo "\n2. ALL USERS WITH WORK TYPES:\n";
    $stmt = $pdo->query("SELECT id, name, email, role, work_type FROM users ORDER BY work_type, name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $wt = $row['work_type'] ?: '(none)';
        echo "   - {$row['id']}: {$row['name']} ({$row['email']}) - role: {$row['role']}, work_type: {$wt}\n";
    }
    
    // 3. Operators by work type
    echo "\n3. OPERATORS BY WORK TYPE:\n";
    $stmt = $pdo->query("
        SELECT work_type, COUNT(*) as count, STRING_AGG(name || ' (' || email || ')', ', ') as operators
        FROM users 
        WHERE role IN ('operator', 'quality_checker', 'scheduler', 'admin') AND work_type IS NOT NULL AND work_type != ''
        GROUP BY work_type
        ORDER BY work_type
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['work_type']} ({$row['count']} operators):\n";
        echo "     {$row['operators']}\n";
    }
    
    // 4. Test login for all key users
    echo "\n4. LOGIN TESTS (password: Password123!):\n";
    $testEmails = [
        'admin@stitchcraft.com',
        'design1@stitchcraft.com',
        'design2@stitchcraft.com',
        'assembly1@stitchcraft.com',
        'assembly2@stitchcraft.com',
        'prod_lead@stitchcraft.com',
        'qc1@stitchcraft.com',
        'qc2@stitchcraft.com',
        'quality_eng@stitchcraft.com',
        'maint1@stitchcraft.com',
        'maint2@stitchcraft.com',
        'assembly3@stitchcraft.com',
        'final_assembly@stitchcraft.com',
        'test_eng1@stitchcraft.com',
        'test_eng2@stitchcraft.com',
        'logistics_coord@stitchcraft.com',
        'warehouse_op@stitchcraft.com',
        'shipping@stitchcraft.com',
        'planner1@stitchcraft.com',
        'planner2@stitchcraft.com',
    ];
    
    $passed = 0;
    foreach ($testEmails as $email) {
        $stmt = $pdo->prepare("SELECT id, name, email, role, work_type, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify('Password123!', $user['password'])) {
            echo "   ✓ {$user['name']} ({$user['email']}) - {$user['role']}, {$user['work_type']}\n";
            $passed++;
        } else {
            echo "   ✗ {$email} - FAILED\n";
        }
    }
    
    echo "\n=== LOGIN TESTS: $passed/" . count($testEmails) . " PASSED ===\n";
    echo "\n=== DATABASE VERIFICATION COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}