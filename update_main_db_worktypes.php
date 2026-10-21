<?php
// Update embroidery_app database with vehicle manufacturing work types
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_app', 'postgres', 'trust');
    echo "Connected to embroidery_app\n\n";
    
    // Check table structure
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'work_types'");
    echo "work_types table columns:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['column_name']} ({$row['data_type']})\n";
    }
    
    // Clear existing work types
    $pdo->exec("DELETE FROM work_types");
    echo "\nCleared existing work types\n";
    
    // Vehicle manufacturing work types
    $workTypes = [
        'vehicle_design' => ['label' => 'Vehicle Design', 'description' => 'CAD modeling, specifications, and design reviews', 'color' => '#3B82F6', 'sort_order' => 1],
        'vehicle_manufacturing' => ['label' => 'Vehicle Manufacturing', 'description' => 'Assembly, production line operations', 'color' => '#10B981', 'sort_order' => 2],
        'quality_control' => ['label' => 'Quality Control', 'description' => 'Inspection, testing, and compliance', 'color' => '#F59E0B', 'sort_order' => 3],
        'maintenance' => ['label' => 'Maintenance', 'description' => 'Preventive and corrective maintenance', 'color' => '#EF4444', 'sort_order' => 4],
        'assembly' => ['label' => 'Assembly', 'description' => 'Final assembly and packaging', 'color' => '#8B5CF6', 'sort_order' => 5],
        'testing' => ['label' => 'Testing', 'description' => 'Functional and performance testing', 'color' => '#EC4899', 'sort_order' => 6],
        'logistics' => ['label' => 'Logistics', 'description' => 'Parts inventory, shipping, and distribution', 'color' => '#06B6D4', 'sort_order' => 7],
        'production_planning' => ['label' => 'Production Planning', 'description' => 'Scheduling and resource allocation', 'color' => '#84CC16', 'sort_order' => 8],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO work_types (name, label, description, color, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, true, NOW())");
    
    foreach ($workTypes as $name => $data) {
        $stmt->execute([$name, $data['label'], $data['description'], $data['color'], $data['sort_order']]);
        echo "Inserted: $name - {$data['label']}\n";
    }
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) FROM work_types");
    $count = $stmt->fetchColumn();
    echo "\nTotal work types: $count\n";
    
    $stmt = $pdo->query("SELECT name, label, description FROM work_types ORDER BY sort_order");
    echo "\nWork types:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['label']}): {$row['description']}\n";
    }
    
    echo "\nDone!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}