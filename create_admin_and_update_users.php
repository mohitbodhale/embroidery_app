<?php
// Create admin user and update work types for existing users
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app_test', 'postgres', 'trust');
    echo "Connected to PostgreSQL successfully\n\n";
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@stitchcraft.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $adminHash = password_hash('Password123!', PASSWORD_BCRYPT);
    
    if ($admin) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = ?, work_type = ? WHERE email = ?");
        $stmt->execute([$adminHash, 'admin', 'vehicle_design', 'admin@stitchcraft.com']);
        echo "Admin user updated\n";
    } else {
        // Insert new admin
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, organization_id, work_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['Admin User', 'admin@stitchcraft.com', $adminHash, 'admin', 1, 'vehicle_design']);
        echo "Admin user created\n";
    }
    
    // Update existing users with vehicle manufacturing work types
    $updates = [
        ['email' => 'scheduler@stitchcraft.com', 'work_type' => 'production_planning'],
        ['email' => 'operator@stitchcraft.com', 'work_type' => 'vehicle_manufacturing'],
        ['email' => 'qc1@stitchcraft.com', 'work_type' => 'quality_control'],
        ['email' => 'production@stitchcraft.com', 'work_type' => 'assembly'],
    ];
    
    $stmt = $pdo->prepare("UPDATE users SET work_type = ? WHERE email = ?");
    foreach ($updates as $update) {
        $stmt->execute([$update['work_type'], $update['email']]);
        echo "Updated {$update['email']} with work_type: {$update['work_type']}\n";
    }
    
    // Verify all users
    $stmt = $pdo->query("SELECT id, name, email, role, work_type FROM users");
    echo "\nAll users after update:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['id']}: {$row['name']} ({$row['email']}) - role: {$row['role']}, work_type: {$row['work_type']}\n";
    }
    
    // Test password verification for admin
    $stmt = $pdo->query("SELECT password FROM users WHERE email = 'admin@stitchcraft.com'");
    $hash = $stmt->fetchColumn();
    echo "\nAdmin password hash: $hash\n";
    
    if (password_verify('Password123!', $hash)) {
        echo "Password 'Password123!' is VALID\n";
    } else {
        echo "Password 'Password123!' is INVALID\n";
    }
    
    // Test password for operator
    $stmt = $pdo->query("SELECT password FROM users WHERE email = 'operator@stitchcraft.com'");
    $hash = $stmt->fetchColumn();
    echo "\nOperator password hash: $hash\n";
    
    if (password_verify('Password123!', $hash)) {
        echo "Password 'Password123!' is VALID\n";
    } else {
        echo "Password 'Password123!' is INVALID\n";
    }
    
    echo "\nDone!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}