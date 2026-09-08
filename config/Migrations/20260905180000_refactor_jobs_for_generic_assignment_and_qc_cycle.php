<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Workflow:
 *   1. Scheduler creates a job
 *   2. Scheduler (or admin) assigns a user to complete the job.
 *      The assigned user has role `operator` and a work_type such as
 *      digitizing, programming, or data_entry.
 *   3. Scheduler assigns a QC user to check the work.
 *   4. QC reviews the output and either:
 *        - approves the job -> it proceeds to production, or
 *        - rejects with notes -> the job is returned to the original assigned user
 *          for fixes (and the cycle can repeat).
 *
 * This migration:
 *   - Adds `assigned_to` (the user who has to do the work) on jobs.
 *   - Adds `assigned_by` (the user who assigned the work — usually a scheduler).
 *   - Adds `assigned_at` timestamp.
 *   - Adds `qc_review_notes` for the QC return reason.
 *   - Adds `qc_reviewed_at` timestamp.
 *   - Adds `qc_status` ('pending' | 'approved' | 'rejected') on jobs.
 *   - Adds `revision_count` to track how many times the job has been
 *     returned from QC.
 *   - Adds a `job_qc_reviews` audit table that records every QC decision
 *     (who reviewed, when, outcome, notes) so we have a full history.
 *   - Adds a `job_assignments` table to log every assignment / reassignment
 *     event (assigned_to, assigned_by, status, timestamps).
 *
 * A single `operator` role is used for all operator types. The operator
 * subtype is stored in `users.work_type` as digitizing, programming, or
 * data_entry. We do NOT add per-role columns to jobs; a single `assigned_to`
 * field is enough.
 */
final class RefactorJobsForGenericAssignmentAndQcCycle extends BaseMigration
{
    public function change(): void
    {
        // -----------------------------------------------------------------
        // 1) Add operator role (if missing)
        // -----------------------------------------------------------------
        if ($this->table('roles')->exists()) {
            $existing = array_column($this->fetchAll('SELECT name FROM roles'), 'name');

            $toInsert = [];
            if (!in_array('operator', $existing, true)) {
                $toInsert[] = [
                    'name'        => 'operator',
                    'label'       => 'Operator',
                    'description' => 'General operator role. Use work_type to define specialty: digitizing, programming, or data_entry.',
                    'sort_order'  => 35,
                    'is_active'   => true,
                    'created_at'  => date('Y-m-d H:i:s'),
                ];
            }
            if (!empty($toInsert)) {
                $this->table('roles')->insert($toInsert)->save();
            }
        }

        // -----------------------------------------------------------------
        // 2) Extend jobs table
        // -----------------------------------------------------------------
        if ($this->table('jobs')->exists()) {
            $jobs = $this->table('jobs');

            $columnExists = function (string $column): bool {
                $row = $this->fetchRow(
                    "SELECT 1 FROM information_schema.columns
                     WHERE table_name = 'jobs' AND column_name = '{$column}'"
                );
                return (bool) $row;
            };

            // assigned_to: the user who has to do the work (any role)
            if (!$columnExists('assigned_to')) {
                $jobs->addColumn('assigned_to', 'integer', [
                    'null'    => true,
                    'after'   => 'created_by',
                ]);
            }

            // assigned_by: the user (usually a scheduler) who assigned the work
            if (!$columnExists('assigned_by')) {
                $jobs->addColumn('assigned_by', 'integer', [
                    'null'    => true,
                    'after'   => 'assigned_to',
                ]);
            }

            // assigned_at: when the work was assigned
            if (!$columnExists('assigned_at')) {
                $jobs->addColumn('assigned_at', 'datetime', [
                    'null'    => true,
                    'after'   => 'assigned_by',
                ]);
            }

            // qc_status: pending | approved | rejected
            if (!$columnExists('qc_status')) {
                $jobs->addColumn('qc_status', 'string', [
                    'limit'   => 16,
                    'null'    => false,
                    'default' => 'pending',
                    'after'   => 'assigned_at',
                ]);
            }

            // qc_review_notes: the QC's notes (used when returning the job)
            if (!$columnExists('qc_review_notes')) {
                $jobs->addColumn('qc_review_notes', 'text', [
                    'null'    => true,
                    'after'   => 'qc_status',
                ]);
            }

            // qc_reviewed_at: when QC last touched the job
            if (!$columnExists('qc_reviewed_at')) {
                $jobs->addColumn('qc_reviewed_at', 'datetime', [
                    'null'    => true,
                    'after'   => 'qc_review_notes',
                ]);
            }

            // revision_count: how many times the job has been returned from QC
            if (!$columnExists('revision_count')) {
                $jobs->addColumn('revision_count', 'integer', [
                    'null'    => false,
                    'default' => 0,
                    'after'   => 'qc_reviewed_at',
                ]);
            }

            $jobs->update();

            // Indexes
            $indexExists = function (string $name): bool {
                $row = $this->fetchRow(
                    "SELECT 1 FROM information_schema.statistics
                     WHERE table_name = 'jobs' AND index_name = '{$name}'"
                );
                return (bool) $row;
            };
            $fkExists = function (string $name): bool {
                $row = $this->fetchRow(
                    "SELECT 1 FROM information_schema.table_constraints
                     WHERE constraint_name = '{$name}' AND table_name = 'jobs'"
                );
                return (bool) $row;
            };

            if (!$indexExists('idx_jobs_assigned_to')) {
                $jobs->addIndex(['assigned_to'], ['name' => 'idx_jobs_assigned_to'])->update();
            }
            if (!$indexExists('idx_jobs_assigned_by')) {
                $jobs->addIndex(['assigned_by'], ['name' => 'idx_jobs_assigned_by'])->update();
            }
            if (!$indexExists('idx_jobs_qc_status')) {
                $jobs->addIndex(['qc_status'], ['name' => 'idx_jobs_qc_status'])->update();
            }

            if (!$fkExists('fk_jobs_assigned_to')) {
                $jobs->addForeignKey('assigned_to', 'users', 'id', [
                    'delete' => 'SET_NULL', 'update' => 'NO_ACTION', 'name' => 'fk_jobs_assigned_to',
                ])->update();
            }
            if (!$fkExists('fk_jobs_assigned_by')) {
                $jobs->addForeignKey('assigned_by', 'users', 'id', [
                    'delete' => 'SET_NULL', 'update' => 'NO_ACTION', 'name' => 'fk_jobs_assigned_by',
                ])->update();
            }
        }

        // -----------------------------------------------------------------
        // 3) job_assignments: log every assignment / reassignment event
        // -----------------------------------------------------------------
        if (!$this->table('job_assignments')->exists()) {
            $this->table('job_assignments')
                ->addColumn('job_id', 'integer', ['null' => false])
                ->addColumn('assigned_to', 'integer', ['null' => false])
                ->addColumn('assigned_by', 'integer', ['null' => true])
                ->addColumn('assigned_at', 'datetime', [
                    'null'    => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('completed_at', 'datetime', ['null' => true])
                ->addColumn('note', 'text', ['null' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['job_id'], ['name' => 'idx_assign_job'])
                ->addIndex(['assigned_to'], ['name' => 'idx_assign_user'])
                ->addIndex(['assigned_by'], ['name' => 'idx_assign_by'])
                ->addForeignKey('job_id', 'jobs', 'id', [
                    'delete' => 'CASCADE', 'update' => 'NO_ACTION',
                ])
                ->addForeignKey('assigned_to', 'users', 'id', [
                    'delete' => 'CASCADE', 'update' => 'NO_ACTION',
                ])
                ->addForeignKey('assigned_by', 'users', 'id', [
                    'delete' => 'SET_NULL', 'update' => 'NO_ACTION',
                ])
                ->create();
        }

        // -----------------------------------------------------------------
        // 4) job_qc_reviews: full audit trail of QC decisions
        // -----------------------------------------------------------------
        if (!$this->table('job_qc_reviews')->exists()) {
            $this->table('job_qc_reviews')
                ->addColumn('job_id', 'integer', ['null' => false])
                ->addColumn('qc_user_id', 'integer', ['null' => false])
                ->addColumn('assigned_to_user_id', 'integer', [
                    'null'    => true,
                    'comment' => 'The user whose work was reviewed (for return-to-sender).',
                ])
                ->addColumn('decision', 'string', [
                    'limit'   => 16,
                    'null'    => false,
                    'comment' => 'approved | rejected',
                ])
                ->addColumn('notes', 'text', ['null' => true])
                ->addColumn('reviewed_at', 'datetime', [
                    'null'    => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['job_id'], ['name' => 'idx_qc_review_job'])
                ->addIndex(['qc_user_id'], ['name' => 'idx_qc_review_user'])
                ->addForeignKey('job_id', 'jobs', 'id', [
                    'delete' => 'CASCADE', 'update' => 'NO_ACTION',
                ])
                ->addForeignKey('qc_user_id', 'users', 'id', [
                    'delete' => 'CASCADE', 'update' => 'NO_ACTION',
                ])
                ->addForeignKey('assigned_to_user_id', 'users', 'id', [
                    'delete' => 'SET_NULL', 'update' => 'NO_ACTION',
                ])
                ->create();
        }
    }
}
