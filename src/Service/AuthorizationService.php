<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Entity\Job;

/**
 * AuthorizationService
 * 
 * Handles role-based authorization and permissions for the embroidery workflow system.
 * 
 * User Roles:
 * - admin: Full system access
 * - scheduler: Can create jobs, upload images, assign to operators
 * - operator: Can download assigned jobs, upload EMB files
 * - qc: Can review submitted EMB files, approve/reject
 * - production: Can download approved EMB files
 */
class AuthorizationService
{
    private const ROLE_HIERARCHY = [
        'admin' => 100,
        'qc' => 40,
        'production' => 30,
        'operator' => 20,
        'scheduler' => 10,
    ];

    private const PERMISSIONS = [
        'admin' => [
            'users.index', 'users.add', 'users.edit', 'users.delete', 'users.view',
            'jobs.index', 'jobs.add', 'jobs.edit', 'jobs.delete', 'jobs.view',
            'jobs.assign', 'jobs.changeStatus', 'jobs.downloadFile',
            'reports.view', 'system.configure'
        ],
        'scheduler' => [
            'jobs.index', 'jobs.add', 'jobs.view', 'jobs.edit',
            'jobs.uploadImage', 'jobs.assign', 'jobs.downloadFile',
            'jobs.viewHistory', 'jobAttachments.index', 'jobAttachments.view'
        ],
        'operator' => [
            'jobs.index', 'jobs.view', 'jobs.viewAssigned',
            'jobs.downloadFile', 'jobs.uploadEMB', 'jobs.submitForReview',
            'jobAttachments.view', 'jobAttachments.index', 'jobAttachments.download'
        ],
        'qc' => [
            'jobs.index', 'jobs.view', 'jobs.viewReview',
            'jobs.approveEMB', 'jobs.rejectEMB', 'jobs.changeStatus',
            'jobs.downloadFile', 'jobAttachments.view', 'jobAttachments.index',
            'jobAttachments.download', 'reports.qc'
        ],
        'production' => [
            'jobs.index', 'jobs.view', 'jobs.viewApproved',
            'jobs.downloadFile', 'jobs.updateStatus', 'jobAttachments.download',
            'jobAttachments.view'
        ],
    ];

    /**
     * Check if user has a specific permission
     */
    public function can(User $user, string $action): bool
    {
        if (!isset(self::PERMISSIONS[$user->role])) {
            return false;
        }

        return in_array($action, self::PERMISSIONS[$user->role], true);
    }

    /**
     * Check if user has any of the specified permissions
     */
    public function canAny(User $user, array $actions): bool
    {
        foreach ($actions as $action) {
            if ($this->can($user, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all specified permissions
     */
    public function canAll(User $user, array $actions): bool
    {
        foreach ($actions as $action) {
            if (!$this->can($user, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user can access a job based on role
     */
    public function canAccessJob(User $user, Job $job): bool
    {
        // Admin can access all jobs
        if ($user->role === 'admin') {
            return true;
        }

        // Must be in same organization
        if ($user->organization_id !== $job->organization_id) {
            return false;
        }

        // Scheduler can access jobs they created or any unassigned job
        if ($user->role === 'scheduler') {
            return $user->id === $job->created_by || $job->operator_id === null;
        }

        // Digitizer can access jobs assigned to them
        if ($user->role === 'operator') {
            return $user->id === $job->operator_id;
        }

        // QC can access jobs in review status
        if ($user->role === 'qc') {
            return in_array($job->status, ['submitted', 'rejected'], true);
        }

        // Production can access approved jobs
        if ($user->role === 'production') {
            return $job->status === 'approved';
        }

        return false;
    }

    /**
     * Check if user can perform a specific job action
     */
    public function canActionJob(User $user, Job $job, string $action): bool
    {
        if (!$this->canAccessJob($user, $job)) {
            return false;
        }

        switch ($action) {
            case 'edit':
                // Only scheduler can edit draft jobs
                return $user->role === 'scheduler' && $job->status === 'draft';

            case 'assign':
                // Only scheduler can assign jobs
                return $user->role === 'scheduler' && $job->status === 'draft';

            case 'uploadImage':
                // Only scheduler can upload initial images
                return $user->role === 'scheduler' && in_array($job->status, ['draft', 'assigned'], true);

            case 'downloadFile':
                // Digitizer, QC, and Production can download
                return in_array($user->role, ['scheduler', 'operator', 'qc', 'production'], true);

            case 'uploadEMB':
                // Only assigned operator can upload EMB files
                return $user->role === 'operator' && $user->id === $job->operator_id 
                    && $job->status === 'assigned';

            case 'submitForReview':
                // Only operator can submit for review
                return $user->role === 'operator' && $user->id === $job->operator_id 
                    && $job->status === 'in_progress';

            case 'approveEMB':
                // Only QC can approve
                return $user->role === 'qc' && $job->status === 'submitted';

            case 'rejectEMB':
                // Only QC can reject
                return $user->role === 'qc' && $job->status === 'submitted';

            case 'updateStatus':
                // Production can update status on approved jobs
                return $user->role === 'production' && $job->status === 'approved';

            default:
                return false;
        }
    }

    /**
     * Get the role hierarchy level (higher = more privileged)
     */
    public function getRoleLevel(string $role): int
    {
        return self::ROLE_HIERARCHY[$role] ?? 0;
    }

    /**
     * Check if user1 has higher privilege than user2
     */
    public function isHigherPrivilege(User $user1, User $user2): bool
    {
        return $this->getRoleLevel($user1->role) > $this->getRoleLevel($user2->role);
    }

    /**
     * Get all permissions for a role
     */
    public function getPermissions(string $role): array
    {
        return self::PERMISSIONS[$role] ?? [];
    }

    /**
     * Get all available roles
     */
    public function getAllRoles(): array
    {
        return array_keys(self::PERMISSIONS);
    }
}
