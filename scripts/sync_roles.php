<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_scheduler', 'postgres', 'root');

// Sync role_id from role for all non-admin users
$stmt = $pdo->exec('
    UPDATE users u
    SET role_id = r.id
    FROM roles r
    WHERE u.role::text = r.name AND u.role_id IS NULL
');
echo "Updated role_ids: {$stmt}\n";

// Verify
$stmt = $pdo->query('SELECT id, name, email, role, role_id FROM users ORDER BY id');
echo "\nAll users after sync:\n";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}, RoleID: " . ($row['role_id'] ?? 'NULL') . "\n";
}
