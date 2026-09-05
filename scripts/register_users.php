<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_scheduler', 'postgres', 'root');
$password = password_hash('Test123456', PASSWORD_DEFAULT);

$orgStmt = $pdo->query('SELECT id FROM organizations WHERE status = \'active\' ORDER BY id ASC LIMIT 1');
$orgId = $orgStmt->fetchColumn();

$users = [
    ['scheduler', 'scheduler@embroidery.local', 'Test Scheduler'],
    ['digitizer', 'digitizer@embroidery.local', 'Test Digitizer'],
    ['quality_checker', 'qc@embroidery.local', 'Test QC'],
    ['production', 'production@embroidery.local', 'Test Production'],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$u[1]]);
    if ($stmt->fetchColumn()) {
        echo "User {$u[2]} already exists\n";
        continue;
    }
    $insertStmt = $pdo->prepare('INSERT INTO users (name, email, password, role, organization_id, role_id, created_at) VALUES (?, ?, ?, ?, ?, NULL, NOW())');
    $insertStmt->execute([$u[2], $u[1], $password, $u[0], $orgId]);
    echo "Registered {$u[2]} ({$u[0]}) - ID: " . $pdo->lastInsertId('users_id_seq') . "\n";
}

// Show all users
echo "\nAll users:\n";
$stmt = $pdo->query('SELECT id, name, email, role, role_id, organization_id FROM users ORDER BY id');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}, RoleID: " . ($row['role_id'] ?? 'NULL') . ", OrgID: {$row['organization_id']}\n";
}
