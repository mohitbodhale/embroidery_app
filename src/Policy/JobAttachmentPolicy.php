<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * Simple policy for job attachment authorization.
 */
class JobAttachmentPolicy
{
    public function canIndex($user, $attachment = null): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        if (in_array($role, ['admin', 'scheduler', 'operator', 'quality_checker', 'production'], true)) {
            return true;
        }
        return false;
    }

    public function canView($user, $attachment): bool
    {
        if (!$user || !$attachment || !$attachment->job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $job = $attachment->job;

        if (in_array($role, ['admin', 'scheduler'], true)) {
            return true;
        }
        if ($role === 'operator') {
            return $job->operator_id == ($user->id ?? null);
        }
        if ($role === 'quality_checker') {
            return $job->qc_id == ($user->id ?? null);
        }
        if ($role === 'production') {
            return in_array($job->status, ['qc_approved', 'in_production'], true);
        }

        return false;
    }

    public function canAdd($user, $job = null): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        if (in_array($role, ['admin', 'scheduler'], true)) {
            return true;
        }
        if ($role === 'operator' && $job && $job->operator_id == ($user->id ?? null)) {
            return true;
        }
        if ($role === 'quality_checker' && $job && $job->qc_id == ($user->id ?? null)) {
            return true;
        }
        if ($role === 'production' && $job && in_array($job->status, ['qc_approved', 'in_production'], true)) {
            return true;
        }

        return false;
    }

    public function canEdit($user, $attachment): bool
    {
        if (!$user || !$attachment) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        if (in_array($role, ['admin', 'scheduler'], true)) {
            return true;
        }
        return $attachment->uploaded_by == ($user->id ?? null);
    }

    public function canDelete($user, $attachment): bool
    {
        if (!$user || !$attachment) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        if (in_array($role, ['admin', 'scheduler'], true)) {
            return true;
        }
        return $attachment->uploaded_by == ($user->id ?? null);
    }

    public function canDownload($user, $attachment): bool
    {
        return $this->canView($user, $attachment);
    }
}
