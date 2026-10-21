<?php
// Check embroidery_app database
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app', 'postgres', 'trust');
    echo "Connected to embroidery_app\n";
    
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'work_types')");
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "work_types table exists\n";
        $stmt = $pdo->query("SELECT COUNT(*) FROM work_types");
        $count = $stmt->fetchColumn();
        echo "Work types count: $count\n";
        
        $stmt = $pdo->query("SELECT name, label FROM work_types ORDER BY name");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['name']} ({$row['label']})\n";
        }
    } else {
        echo "work_types table does NOT exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}