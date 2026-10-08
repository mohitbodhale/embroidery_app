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
        // Only schedulers and admins create jobs
        return in_array($role, ['scheduler', 'admin'], true);
    }

    public function canEdit($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'scheduler') {
            return !empty($job)
                && $job->created_by == $userId
                && $job->status === 'draft';
        }

        if ($role === 'operator') {
            return !empty($job) && ($job->operator_id == $userId) && in_array($job->status, ['in_progress', 'qc_rejected'], true);
        }

        if ($role === 'quality_checker') {
            return !empty($job) && ($job->qc_id == $userId) && ($job->status === 'ready_for_qc');
        }

        if ($role === 'production') {
            return !empty($job) && ($job->status === 'in_production');
        }

        return false;
    }

    public function canDelete($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'scheduler') {
            return !empty($job) && ($job->created_by == $userId);
        }

        return false;
    }

    public function canAssign($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        if ($role === 'admin') {
            return true;
        }
        if ($role === 'scheduler') {
            $userId = $user->id ?? ($user['id'] ?? null);
            return !empty($job)
                && $job->created_by == $userId
                && !in_array($job->status, ['completed', 'cancelled'], true);
        }

        return false;
    }

    public function canApprove($user, $job): bool
    {
        if (!$user) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);
        return $role === 'admin'
            || ($role === 'quality_checker' && (!is_object($job) || $job->qc_id == $userId));
    }

    public function canSubmit($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);

        return $role === 'admin' || ($role === 'operator' && $job->operator_id == $userId);
    }

    public function canProduce($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));

        return in_array($role, ['admin', 'production'], true);
    }

public function canView($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);
        if ($role === 'admin') {
            return true;
        }
        if ($role === 'production') {
            return in_array($job->status, ['qc_approved', 'in_production'], true);
        }
        if ($role === 'scheduler') {
            return $job->created_by == $userId;
        }
        if ($role === 'operator') {
            return $job->operator_id == $userId;
        }
        if ($role === 'quality_checker') {
            return $job->qc_id == $userId;
        }

        return false;
    }
}
