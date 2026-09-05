<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
$this->assign('title', 'Users Export');
$filename = 'users_export_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Organization', 'Created']);
foreach ($users as $user) {
    fputcsv($output, [
        $user->id,
        $user->name,
        $user->email,
        $user->role,
        $user->hasValue('organization') ? $user->organization->name : '',
        $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : ''
    ]);
}
fclose($output);
