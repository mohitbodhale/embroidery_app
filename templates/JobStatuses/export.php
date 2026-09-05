<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\JobStatus> $jobStatuses
 */
$this->assign('title', 'Job Statuses Export');
$filename = 'job_statuses_export_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Label', 'Description', 'Color', 'Active', 'Terminal', 'Sort Order', 'Jobs Count']);
foreach ($jobStatuses as $jobStatus) {
    fputcsv($output, [
        $jobStatus->id,
        $jobStatus->name,
        $jobStatus->label,
        $jobStatus->description ?? '',
        $jobStatus->color,
        $jobStatus->is_active ? 'Yes' : 'No',
        $jobStatus->is_terminal ? 'Yes' : 'No',
        $jobStatus->sort_order,
        count($jobStatus->jobs ?? [])
    ]);
}
fclose($output);
