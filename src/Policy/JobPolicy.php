<?php
declare(strict_types=1);

namespace App\Policy;

/**
 * Simple policy for job authorization.
 * Methods return boolean: true = allowed, false = denied.
 */
class JobPolicy
{
    public function canCreate($user, $jobData = null): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        // Only schedulers create jobs (admin manages users, not job creation)
        return in_array($role, ['scheduler'], true);
    }

    public function canEdit($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);

        if (in_array($role, ['admin', 'scheduler'], true)) {
            return true;
        }

        if ($role === 'digitizer') {
            // Digitizer can edit only if assigned to job
            return !empty($job) && ($job->digitizer_id == $userId);
        }

        if ($role === 'quality_checker') {
            // QC can edit only if assigned to job (for adding attachments/notes)
            return !empty($job) && ($job->qc_id == $userId);
        }

        return false;
    }

    public function canDelete($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        return $role === 'admin';
    }

    public function canAssign($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        // Admins and schedulers can assign digitizers/qc
        return in_array($role, ['admin', 'scheduler'], true);
    }

    public function canApprove($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);
        return $role === 'quality_checker' && (!is_object($job) || $job->qc_id == $userId);
    }

    public function canSubmit($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);

        return $role === 'digitizer' && $job->digitizer_id == $userId;
    }

    public function canProduce($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));

        return $role === 'production';
    }

    public function canView($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);
        if (in_array($role, ['admin', 'scheduler', 'production'], true)) {
            return true;
        }
        if ($role === 'digitizer') {
            return $job->digitizer_id == $userId;
        }
        if ($role === 'quality_checker') {
            return $job->qc_id == $userId;
        }

        return false;
    }
}
