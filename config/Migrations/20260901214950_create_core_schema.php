<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class CreateCoreSchema extends BaseMigration
{
    public function change(): void
    {
        // 1) Organizations
        if (!$this->table('organizations')->exists()) {
            $this->table('organizations')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('domain_or_slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('status', 'string', ['limit' => 32, 'null' => false, 'default' => 'active'])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['domain_or_slug'], ['unique' => true])
                ->create();
        }

        // 2) Roles (master lookup for user roles)
        if (!$this->table('roles')->exists()) {
            $this->table('roles')
                ->addColumn('name', 'string', ['limit' => 64, 'null' => false])
                ->addColumn('label', 'string', ['limit' => 128, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('color', 'string', ['limit' => 16, 'null' => true])
                ->addColumn('sort_order', 'integer', ['null' => true])
                ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['name'], ['unique' => true])
                ->create();

            // Seed default roles
            $this->table('roles')
                ->insert([
                    ['name' => 'admin',          'label' => 'Admin',          'sort_order' => 10, 'is_active' => true],
                    ['name' => 'scheduler',      'label' => 'Scheduler',      'sort_order' => 20, 'is_active' => true],
                    ['name' => 'digitizer',      'label' => 'Digitizer',      'sort_order' => 30, 'is_active' => true],
                    ['name' => 'quality_checker','label' => 'Quality Checker','sort_order' => 40, 'is_active' => true],
                    ['name' => 'production',     'label' => 'Production',     'sort_order' => 50, 'is_active' => true],
                    ['name' => 'pending',        'label' => 'Pending',        'sort_order' => 0,  'is_active' => true],
                ])
                ->save();
        }

        // 3) Users
        if (!$this->table('users')->exists()) {
            $this->table('users')
                ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('password', 'text', ['null' => false])
                ->addColumn('role', 'string', ['limit' => 50, 'null' => false, 'default' => 'digitizer'])
                ->addColumn('organization_id', 'integer', ['null' => false])
                ->addColumn('role_id', 'integer', ['null' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['organization_id'])
                ->addIndex(['role_id'])
                ->addIndex(['organization_id', 'email'], ['unique' => true, 'name' => 'users_unique_email_per_org'])
                ->create();

            $this->table('users')
                ->addForeignKey('organization_id', 'organizations', 'id', ['delete' => 'RESTRICT', 'update' => 'NO_ACTION'])
                ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // 4) Job statuses (master lookup for job workflow states)
        if (!$this->table('job_statuses')->exists()) {
            $this->table('job_statuses')
                ->addColumn('name', 'string', ['limit' => 64, 'null' => false])
                ->addColumn('label', 'string', ['limit' => 128, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('color', 'string', ['limit' => 16, 'null' => true])
                ->addColumn('sort_order', 'integer', ['null' => true])
                ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
                ->addColumn('is_terminal', 'boolean', ['null' => false, 'default' => false])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['name'], ['unique' => true])
                ->create();

            // Seed workflow statuses
            $this->table('job_statuses')->insert([
                ['name' => 'draft',           'label' => 'Draft',            'color' => '#6c757d', 'sort_order' => 10, 'is_active' => true, 'is_terminal' => false],
                ['name' => 'in_digitizing',   'label' => 'In Digitizing',    'color' => '#0d6efd', 'sort_order' => 20, 'is_active' => true, 'is_terminal' => false],
                ['name' => 'digitized',       'label' => 'Digitized',        'color' => '#6610f2', 'sort_order' => 30, 'is_active' => true, 'is_terminal' => false],
                ['name' => 'qc_approved',     'label' => 'QC Approved',      'color' => '#198754', 'sort_order' => 40, 'is_active' => true, 'is_terminal' => false],
                ['name' => 'qc_rejected',     'label' => 'QC Rejected',      'color' => '#dc3545', 'sort_order' => 50, 'is_active' => true, 'is_terminal' => false],
                ['name' => 'in_production',   'label' => 'In Production',    'color' => '#fd7e14', 'sort_order' => 60, 'is_active' => true, 'is_terminal' => false],
                ['name' => 'completed',       'label' => 'Completed',        'color' => '#20c997', 'sort_order' => 70, 'is_active' => true, 'is_terminal' => true],
                ['name' => 'cancelled',       'label' => 'Cancelled',        'color' => '#6c757d', 'sort_order' => 80, 'is_active' => true, 'is_terminal' => true],
            ])->save();
        }

        // 5) Jobs
        if (!$this->table('jobs')->exists()) {
            $this->table('jobs')
                ->addColumn('job_number', 'string', ['limit' => 64, 'null' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('instructions', 'text', ['null' => true])
                ->addColumn('status', 'string', ['limit' => 32, 'null' => false, 'default' => 'draft'])
                ->addColumn('created_by', 'integer', ['null' => true])
                ->addColumn('digitizer_id', 'integer', ['null' => true])
                ->addColumn('qc_id', 'integer', ['null' => true])
                ->addColumn('scheduled_date', 'datetime', ['null' => true])
                ->addColumn('status_id', 'integer', ['null' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('organization_id', 'integer', ['null' => false])
                ->addIndex(['organization_id'])
                ->addIndex(['status'])
                ->addIndex(['status_id'])
                ->addIndex(['organization_id', 'job_number'], ['unique' => true, 'name' => 'unique_job_number_per_org'])
                ->create();

            $this->table('jobs')
                ->addForeignKey('organization_id', 'organizations', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->addForeignKey('digitizer_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->addForeignKey('qc_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->addForeignKey('status_id', 'job_statuses', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // 6) Job Attachments
        if (!$this->table('job_attachments')->exists()) {
            $this->table('job_attachments')
                ->addColumn('job_id', 'integer', ['null' => false])
                ->addColumn('file_name', 'string', ['limit' => 512, 'null' => false])
                ->addColumn('file_path', 'string', ['limit' => 1024, 'null' => false])
                ->addColumn('file_type', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('file_size', 'integer', ['null' => true])
                ->addColumn('mime_type', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('comments', 'text', ['null' => true])
                ->addColumn('uploaded_by', 'integer', ['null' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['job_id'])
                ->create();

            $this->table('job_attachments')
                ->addForeignKey('job_id', 'jobs', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addForeignKey('uploaded_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }

        // 7) Job Logs (activity trail)
        if (!$this->table('job_logs')->exists()) {
            $this->table('job_logs')
                ->addColumn('job_id', 'integer', ['null' => true])
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('action', 'string', ['limit' => 128, 'null' => false])
                ->addColumn('comments', 'text', ['null' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['job_id'])
                ->addIndex(['user_id'])
                ->create();

            $this->table('job_logs')
                ->addForeignKey('job_id', 'jobs', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }
    }
}
