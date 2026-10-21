<?php
// Test database connection
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app_test', 'postgres', 'trust');
    echo "Connected to PostgreSQL successfully\n";
    
    // Check if work_types table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'work_types')");
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "work_types table exists\n";
        
        // Count work types
        $stmt = $pdo->query("SELECT COUNT(*) FROM work_types");
        $count = $stmt->fetchColumn();
        echo "Work types count: $count\n";
        
        // List work types
        $stmt = $pdo->query("SELECT name, label, description FROM work_types ORDER BY sort_order");
        echo "\nWork types:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['name']} ({$row['label']}): {$row['description']}\n";
        }
    } else {
        echo "work_types table does NOT exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}