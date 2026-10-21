<?php
// Create multiple operators with different work types
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app_test', 'postgres', 'trust');
    echo "Connected to PostgreSQL successfully\n\n";
    
    // Define operators with their work types
    $operators = [
        // Vehicle Design operators
        ['name' => 'Design Engineer 1', 'email' => 'design1@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_design'],
        ['name' => 'Design Engineer 2', 'email' => 'design2@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_design'],
        ['name' => 'CAD Specialist', 'email' => 'cad_specialist@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_design'],
        
        // Vehicle Manufacturing operators
        ['name' => 'Assembly Tech 1', 'email' => 'assembly1@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_manufacturing'],
        ['name' => 'Assembly Tech 2', 'email' => 'assembly2@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_manufacturing'],
        ['name' => 'Production Line Lead', 'email' => 'prod_lead@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_manufacturing'],
        ['name' => 'Manufacturing Operator', 'email' => 'mfg_operator@stitchcraft.com', 'role' => 'operator', 'work_type' => 'vehicle_manufacturing'],
        
        // Quality Control operators
        ['name' => 'QC Inspector 1', 'email' => 'qc1@stitchcraft.com', 'role' => 'quality_checker', 'work_type' => 'quality_control'],
        ['name' => 'QC Inspector 2', 'email' => 'qc2@stitchcraft.com', 'role' => 'quality_checker', 'work_type' => 'quality_control'],
        ['name' => 'Quality Engineer', 'email' => 'quality_eng@stitchcraft.com', 'role' => 'quality_checker', 'work_type' => 'quality_control'],
        
        // Maintenance operators
        ['name' => 'Maintenance Tech 1', 'email' => 'maint1@stitchcraft.com', 'role' => 'operator', 'work_type' => 'maintenance'],
        ['name' => 'Maintenance Tech 2', 'email' => 'maint2@stitchcraft.com', 'role' => 'operator', 'work_type' => 'maintenance'],
        
        // Assembly operators
        ['name' => 'Assembly Tech 3', 'email' => 'assembly3@stitchcraft.com', 'role' => 'operator', 'work_type' => 'assembly'],
        ['name' => 'Final Assembly Lead', 'email' => 'final_assembly@stitchcraft.com', 'role' => 'operator', 'work_type' => 'assembly'],
        
        // Testing operators
        ['name' => 'Test Engineer 1', 'email' => 'test_eng1@stitchcraft.com', 'role' => 'operator', 'work_type' => 'testing'],
        ['name' => 'Test Engineer 2', 'email' => 'test_eng2@stitchcraft.com', 'role' => 'operator', 'work_type' => 'testing'],
        
        // Logistics operators
        ['name' => 'Logistics Coordinator', 'email' => 'logistics_coord@stitchcraft.com', 'role' => 'operator', 'work_type' => 'logistics'],
        ['name' => 'Warehouse Operator', 'email' => 'warehouse_op@stitchcraft.com', 'role' => 'operator', 'work_type' => 'logistics'],
        ['name' => 'Shipping Clerk', 'email' => 'shipping@stitchcraft.com', 'role' => 'operator', 'work_type' => 'logistics'],
        
        // Production Planning operators
        ['name' => 'Production Planner 1', 'email' => 'planner1@stitchcraft.com', 'role' => 'scheduler', 'work_type' => 'production_planning'],
        ['name' => 'Production Planner 2', 'email' => 'planner2@stitchcraft.com', 'role' => 'scheduler', 'work_type' => 'production_planning'],
    ];
    
    $passwordHash = password_hash('Password123!', PASSWORD_BCRYPT);
    
    $created = 0;
    $updated = 0;
    
    foreach ($operators as $op) {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$op['email']]);
        $exists = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($exists) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = ?, work_type = ? WHERE email = ?");
            $stmt->execute([$op['name'], $passwordHash, $op['role'], $op['work_type'], $op['email']]);
            $updated++;
            echo "Updated: {$op['name']} ({$op['email']}) - {$op['work_type']}\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, organization_id, work_type, created_at) VALUES (?, ?, ?, ?, 1, ?, NOW())");
            $stmt->execute([$op['name'], $op['email'], $passwordHash, $op['role'], $op['work_type']]);
            $created++;
            echo "Created: {$op['name']} ({$op['email']}) - {$op['work_type']}\n";
        }
    }
    
    echo "\nTotal created: $created, updated: $updated\n";
    
    // Verify all operators by work type
    echo "\n=== Operators by Work Type ===\n";
    $stmt = $pdo->query("
        SELECT work_type, COUNT(*) as count, STRING_AGG(name || ' (' || email || ')', ', ') as operators
        FROM users 
        WHERE role IN ('operator', 'quality_checker', 'scheduler')
        GROUP BY work_type
        ORDER BY work_type
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['work_type']} ({$row['count']} operators):\n";
        echo "  {$row['operators']}\n";
    }
    
    // Test login for a few
    echo "\n=== Login Tests ===\n";
    $testEmails = ['design1@stitchcraft.com', 'assembly1@stitchcraft.com', 'qc1@stitchcraft.com', 'maint1@stitchcraft.com', 'logistics_coord@stitchcraft.com'];
    foreach ($testEmails as $email) {
        $stmt = $pdo->prepare("SELECT id, name, email, role, work_type, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify('Password123!', $user['password'])) {
            echo "  ✓ {$user['name']} ({$user['email']}) - role: {$user['role']}, work_type: {$user['work_type']}\n";
        } else {
            echo "  ✗ {$email} - LOGIN FAILED\n";
        }
    }
    
    echo "\nDone!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}