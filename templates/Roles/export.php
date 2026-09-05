<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Role> $roles
 */
$this->assign('title', 'Roles Export');
$filename = 'roles_export_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Label', 'Description', 'Color', 'Active', 'Sort Order', 'Users Count']);
foreach ($roles as $role) {
    fputcsv($output, [
        $role->id,
        $role->name,
        $role->label,
        $role->description ?? '',
        $role->color,
        $role->is_active ? 'Yes' : 'No',
        $role->sort_order,
        count($role->users ?? [])
    ]);
}
fclose($output);
