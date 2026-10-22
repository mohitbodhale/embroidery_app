<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class InitialSchema extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-up-method
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('cake_seeds')
            ->addColumn('plugin', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('seed_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('executed_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('job_assignments')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('assigned_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('assigned_by', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('assigned_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('completed_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('note', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('job_id')
                    ->setName('idx_assign_job')
            )
            ->addIndex(
                $this->index('assigned_to')
                    ->setName('idx_assign_user')
            )
            ->addIndex(
                $this->index('assigned_by')
                    ->setName('idx_assign_by')
            )
            ->create();

        $this->table('job_attachments')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('file_name', 'string', [
                'default' => null,
                'limit' => 512,
                'null' => false,
            ])
            ->addColumn('file_path', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => false,
            ])
            ->addColumn('file_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('file_size', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('mime_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('comments', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('uploaded_by', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('job_id')
                    ->setName('job_attachments_job_id')
            )
            ->create();

        $this->table('job_counters')
            ->addColumn('last_number', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $this->table('job_logs')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('action', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('comments', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('job_id')
                    ->setName('job_logs_job_id')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('job_logs_user_id')
            )
            ->create();

        $this->table('job_qc_reviews')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('qc_user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('assigned_to_user_id', 'integer', [
                'comment' => 'The user whose work was reviewed (for return-to-sender).',
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('decision', 'string', [
                'comment' => 'approved | rejected',
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reviewed_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('job_id')
                    ->setName('idx_qc_review_job')
            )
            ->addIndex(
                $this->index('qc_user_id')
                    ->setName('idx_qc_review_user')
            )
            ->create();

        $this->table('job_statuses')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('label', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('color', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_terminal', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('job_statuses_name')
                    ->setType('unique')
            )
            ->create();

        $this->table('jobs')
            ->addColumn('job_number', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('instructions', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'draft',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('assigned_to', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('assigned_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('assigned_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('qc_status', 'string', [
                'default' => 'pending',
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('qc_review_notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('qc_reviewed_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('revision_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('operator_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('qc_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('level_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('level_payment', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('scheduled_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('status_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('organization_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'organization_id',
                        'job_number',
                    ])
                    ->setName('unique_job_number_per_org')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('organization_id')
                    ->setName('jobs_organization_id')
            )
            ->addIndex(
                $this->index('status')
                    ->setName('jobs_status')
            )
            ->addIndex(
                $this->index('status_id')
                    ->setName('jobs_status_id')
            )
            ->addIndex(
                $this->index('created_by')
                    ->setName('jobs_created_by')
            )
            ->addIndex(
                $this->index('operator_id')
                    ->setName('jobs_operator_id')
            )
            ->addIndex(
                $this->index('qc_id')
                    ->setName('jobs_qc_id')
            )
            ->addIndex(
                $this->index('assigned_to')
                    ->setName('idx_jobs_assigned_to')
            )
            ->addIndex(
                $this->index('assigned_by')
                    ->setName('idx_jobs_assigned_by')
            )
            ->addIndex(
                $this->index('qc_status')
                    ->setName('idx_jobs_qc_status')
            )
            ->addIndex(
                $this->index('level_id')
                    ->setName('idx_jobs_level_id')
            )
            ->create();

        $this->table('level_rates')
            ->addColumn('level_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('payment_amount', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('valid_from', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('valid_to', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index([
                        'level_id',
                        'valid_from',
                    ])
                    ->setName('idx_level_rates_level_from')
            )
            ->create();

        $this->table('levels')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('label', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payment_amount', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('color', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('idx_levels_name')
                    ->setType('unique')
            )
            ->create();

        $this->table('operator_wallets')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('balance', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('total_earned', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('total_paid', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_operator_wallets_user_id')
                    ->setType('unique')
            )
            ->create();

        $this->table('organizations')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('domain_or_slug', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'default' => 'active',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('domain_or_slug')
                    ->setName('organizations_domain_or_slug')
                    ->setType('unique')
            )
            ->create();

        $this->table('password_reset_tokens')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('expires_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('used', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('token')
                    ->setName('password_reset_tokens_token')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('password_reset_tokens_user_id')
            )
            ->addIndex(
                $this->index('expires_at')
                    ->setName('password_reset_tokens_expires_at')
            )
            ->create();

        $this->table('roles')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('label', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('color', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('roles_name')
                    ->setType('unique')
            )
            ->create();

        $this->table('user_details')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('bio', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('avatar', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('website', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('location', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('work_type_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('user_details_user_id')
                    ->setType('unique')
            )
            ->create();

        $this->table('users')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('role', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('organization_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('must_change_password', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('work_type', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'organization_id',
                        'email',
                    ])
                    ->setName('users_unique_email_per_org')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('organization_id')
                    ->setName('users_organization_id')
            )
            ->addIndex(
                $this->index('role_id')
                    ->setName('users_role_id')
            )
            ->create();

        $this->table('wallet_transactions')
            ->addColumn('wallet_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => 'earning',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('balance_after', 'decimal', [
                'default' => '0',
                'null' => false,
                'precision' => 12,
                'scale' => 2,
            ])
            ->addColumn('notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reference', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('wallet_id')
                    ->setName('idx_wallet_transactions_wallet_id')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_wallet_transactions_user_id')
            )
            ->addIndex(
                $this->index('job_id')
                    ->setName('idx_wallet_transactions_job_id')
            )
            ->addIndex(
                $this->index('type')
                    ->setName('idx_wallet_transactions_type')
            )
            ->addIndex(
                $this->index('created_at')
                    ->setName('idx_wallet_transactions_created_at')
            )
            ->create();

        $this->table('work_types')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('label', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('color', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestampfractional', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('work_types_name')
                    ->setType('unique')
            )
            ->create();

        $this->table('job_assignments')
            ->addForeignKey(
                $this->foreignKey('assigned_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_assignments_assigned_by_fkey')
            )
            ->addForeignKey(
                $this->foreignKey('assigned_to')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_assignments_assigned_to_fkey')
            )
            ->addForeignKey(
                $this->foreignKey('job_id')
                    ->setReferencedTable('jobs')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_assignments_job_id_fkey')
            )
            ->update();

        $this->table('job_attachments')
            ->addForeignKey(
                $this->foreignKey('job_id')
                    ->setReferencedTable('jobs')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_attachments_job_id_fkey')
            )
            ->addForeignKey(
                $this->foreignKey('uploaded_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_attachments_uploaded_by_fkey')
            )
            ->update();

        $this->table('job_logs')
            ->addForeignKey(
                $this->foreignKey('job_id')
                    ->setReferencedTable('jobs')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_logs_job_id_fkey')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_logs_user_id_fkey')
            )
            ->update();

        $this->table('job_qc_reviews')
            ->addForeignKey(
                $this->foreignKey('assigned_to_user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_qc_reviews_assigned_to_user_id_fkey')
            )
            ->addForeignKey(
                $this->foreignKey('job_id')
                    ->setReferencedTable('jobs')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_qc_reviews_job_id_fkey')
            )
            ->addForeignKey(
                $this->foreignKey('qc_user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('job_qc_reviews_qc_user_id_fkey')
            )
            ->update();

        $this->table('jobs')
            ->addForeignKey(
                $this->foreignKey('status_id')
                    ->setReferencedTable('job_statuses')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('jobs_status_id')
            )
            ->addForeignKey(
                $this->foreignKey('qc_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('jobs_qc_id')
            )
            ->addForeignKey(
                $this->foreignKey('organization_id')
                    ->setReferencedTable('organizations')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('jobs_organization_id')
            )
            ->addForeignKey(
                $this->foreignKey('operator_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('jobs_operator_id')
            )
            ->addForeignKey(
                $this->foreignKey('created_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('jobs_created_by')
            )
            ->addForeignKey(
                $this->foreignKey('level_id')
                    ->setReferencedTable('levels')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_jobs_level_id')
            )
            ->addForeignKey(
                $this->foreignKey('assigned_to')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_jobs_assigned_to')
            )
            ->addForeignKey(
                $this->foreignKey('assigned_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_jobs_assigned_by')
            )
            ->update();

        $this->table('level_rates')
            ->addForeignKey(
                $this->foreignKey('level_id')
                    ->setReferencedTable('levels')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('CASCADE')
                    ->setName('level_rates_level_id_fkey')
            )
            ->update();

        $this->table('operator_wallets')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_operator_wallets_user_id')
            )
            ->update();

        $this->table('password_reset_tokens')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('password_reset_tokens_user_id_fkey')
            )
            ->update();

        $this->table('users')
            ->addForeignKey(
                $this->foreignKey('role_id')
                    ->setReferencedTable('roles')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('users_role_id')
            )
            ->addForeignKey(
                $this->foreignKey('organization_id')
                    ->setReferencedTable('organizations')
                    ->setReferencedColumns('id')
                    ->setDelete('RESTRICT')
                    ->setUpdate('NO_ACTION')
                    ->setName('users_organization_id')
            )
            ->update();

        $this->table('wallet_transactions')
            ->addForeignKey(
                $this->foreignKey('created_by')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_wallet_transactions_created_by')
            )
            ->addForeignKey(
                $this->foreignKey('job_id')
                    ->setReferencedTable('jobs')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_wallet_transactions_job_id')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_wallet_transactions_user_id')
            )
            ->addForeignKey(
                $this->foreignKey('wallet_id')
                    ->setReferencedTable('operator_wallets')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('fk_wallet_transactions_wallet_id')
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-down-method
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('job_assignments')
            ->dropForeignKey(
                'assigned_by'
            )
            ->dropForeignKey(
                'assigned_to'
            )
            ->dropForeignKey(
                'job_id'
            )->save();

        $this->table('job_attachments')
            ->dropForeignKey(
                'job_id'
            )
            ->dropForeignKey(
                'uploaded_by'
            )->save();

        $this->table('job_logs')
            ->dropForeignKey(
                'job_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('job_qc_reviews')
            ->dropForeignKey(
                'assigned_to_user_id'
            )
            ->dropForeignKey(
                'job_id'
            )
            ->dropForeignKey(
                'qc_user_id'
            )->save();

        $this->table('jobs')
            ->dropForeignKey(
                'status_id'
            )
            ->dropForeignKey(
                'qc_id'
            )
            ->dropForeignKey(
                'organization_id'
            )
            ->dropForeignKey(
                'operator_id'
            )
            ->dropForeignKey(
                'created_by'
            )
            ->dropForeignKey(
                'level_id'
            )
            ->dropForeignKey(
                'assigned_to'
            )
            ->dropForeignKey(
                'assigned_by'
            )->save();

        $this->table('level_rates')
            ->dropForeignKey(
                'level_id'
            )->save();

        $this->table('operator_wallets')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('password_reset_tokens')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('users')
            ->dropForeignKey(
                'role_id'
            )
            ->dropForeignKey(
                'organization_id'
            )->save();

        $this->table('wallet_transactions')
            ->dropForeignKey(
                'created_by'
            )
            ->dropForeignKey(
                'job_id'
            )
            ->dropForeignKey(
                'user_id'
            )
            ->dropForeignKey(
                'wallet_id'
            )->save();

        $this->table('cake_seeds')->drop()->save();
        $this->table('job_assignments')->drop()->save();
        $this->table('job_attachments')->drop()->save();
        $this->table('job_counters')->drop()->save();
        $this->table('job_logs')->drop()->save();
        $this->table('job_qc_reviews')->drop()->save();
        $this->table('job_statuses')->drop()->save();
        $this->table('jobs')->drop()->save();
        $this->table('level_rates')->drop()->save();
        $this->table('levels')->drop()->save();
        $this->table('operator_wallets')->drop()->save();
        $this->table('organizations')->drop()->save();
        $this->table('password_reset_tokens')->drop()->save();
        $this->table('roles')->drop()->save();
        $this->table('user_details')->drop()->save();
        $this->table('users')->drop()->save();
        $this->table('wallet_transactions')->drop()->save();
        $this->table('work_types')->drop()->save();
    }
}
