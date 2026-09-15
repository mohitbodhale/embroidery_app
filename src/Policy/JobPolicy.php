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
        \Cake\Log\Log::write('debug', 'JobPolicy::canEdit called');
        \Cake\Log\Log::write('debug', 'User: ' . print_r($user, true));
        \Cake\Log\Log::write('debug', 'Job: ' . print_r($job ? $job->toArray() : 'null', true));

        if (!$user) {
            \Cake\Log\Log::write('debug', 'User is null/false');
            return false;
        }
        $role = strtolower((string)($user->role ?? ($user['role'] ?? '')));
        $userId = $user->id ?? ($user['id'] ?? null);
        \Cake\Log\Log::write('debug', "Role: '$role', UserId: $userId");

        if (in_array($role, ['admin', 'scheduler'], true)) {
            \Cake\Log\Log::write('debug', 'Returning true for admin/scheduler');
            return true;
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
        // Admins and schedulers can assign operators/qc
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

        return $role === 'operator' && $job->operator_id == $userId;
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
        if (in_array($role, ['admin', 'production'], true)) {
            return true;
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
