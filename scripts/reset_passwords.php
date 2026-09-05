<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=embroidery_scheduler', 'postgres', 'root');

$password = password_hash('password123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
$result = $stmt->execute([$password, 'admin@stitchcraft.com']);
echo "Updated admin password: " . ($result ? "OK" : "FAILED") . "\n";

// Verify
$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute(['admin@stitchcraft.com']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Admin: ID={$row['id']}, Name={$row['name']}, Email={$row['email']}\n";
echo "Password hash: " . substr($row['password'], 0, 40) . "...\n";
echo "Password verify: " . (password_verify('password123', $row['password']) ? 'OK' : 'FAILED') . "\n";

// Also reset the other test users' passwords
$users = [
    'scheduler@embroidery.local' => 'Test Scheduler',
    'digitizer@embroidery.local' => 'Test Digitizer',
    'qc@embroidery.local' => 'Test QC',
    'production@embroidery.local' => 'Test Production',
];
foreach ($users as $email => $name) {
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$password, $email]);
    echo "Updated {$name} password: OK\n";
}
