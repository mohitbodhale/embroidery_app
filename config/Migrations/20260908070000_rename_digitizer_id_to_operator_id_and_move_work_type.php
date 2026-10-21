<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class RenameDigitizerIdToOperatorIdAndMoveWorkType extends BaseMigration
{
    private function columnExists(string $table, string $column): bool
    {
        try {
            $schema = $this->connection()->getSchemaCollection();
            if ($schema->describe($table)->hasColumn($column)) {
                return true;
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }
        return false;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $schema = $this->connection()->getSchemaCollection();
            $tableSchema = $schema->describe($table);
            foreach ($tableSchema->getIndexes() as $idx) {
                if ($idx->getName() === $indexName) {
                    return true;
                }
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }
        return false;
    }

    public function change(): void
    {
        if (!$this->table('jobs')->exists() || !$this->table('user_details')->exists()) {
            return;
        }

        // 1) Add operator_id to jobs if missing.
        if (!$this->columnExists('jobs', 'operator_id')) {
            $jobs = $this->table('jobs');
            $jobs->addColumn('operator_id', 'integer', ['null' => true]);
            $jobs->update();
        }

        // 2) Copy existing digitizer_id data to operator_id.
        // Use CakePHP query builder for DB-agnostic UPDATE
        try {
            $this->update('jobs')
                ->set(['operator_id' => new \Cake\Database\Expression\IdentifierExpression('digitizer_id')])
                ->where(['digitizer_id IS NOT NULL' => true])
                ->execute();
        } catch (\Throwable) {
            // Fallback for drivers that don't support this syntax
            $connection = $this->getAdapter()->getConnection();
            $connection->execute('UPDATE jobs SET operator_id = digitizer_id WHERE digitizer_id IS NOT NULL');
        }

        // 3) Drop old digitizer_id column if it is still present.
        if ($this->columnExists('jobs', 'digitizer_id')) {
            $jobs = $this->table('jobs');
            $jobs->removeColumn('digitizer_id');
            $jobs->update();
        }

        // 4) Add work_type_id to user_details if missing.
        if (!$this->columnExists('user_details', 'work_type_id')) {
            $ud = $this->table('user_details');
            $ud->addColumn('work_type_id', 'integer', ['null' => true]);
            $ud->update();
        }

        // 5) Remove work_type from users if present (moved to user_details.work_type_id).
        if ($this->columnExists('users', 'work_type')) {
            $users = $this->table('users');
            $users->removeColumn('work_type');
            $users->update();
        }

        // 6) Add index on operator_id if missing
        if (!$this->indexExists('jobs', 'idx_jobs_operator_id')) {
            $jobs = $this->table('jobs');
            $jobs->addIndex(['operator_id'], ['name' => 'idx_jobs_operator_id']);
            $jobs->update();
        }
    }
}
