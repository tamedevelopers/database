<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Support\Collections\Collection;
use Tamedevelopers\Database\Console\Commands\CommandHelper;

class DBCommand extends CommandHelper
{   
    
    /**
     * Default entry when running command
     */
    public function handle(array $args = [], array $options = []): int
    {
        Logger::writeln('<yellow>Usage:</yellow>');
        Logger::writeln('  php tame db:seed');
        Logger::writeln('  php tame db:wipe');
        Logger::writeln('');

        return 0;
    }

    /**
     * Seed the database with records
     * Subcommand: php tame db:seed
     */
    public function seed(array $args = [], array $options = []): int
    {
        $this->forceChecker($options);

        $key = Manager::regenerate();
        if ( !$key) {
            Logger::error('Failed to generate the application key.');
            return 0;
        }

        Logger::success("Application key generated: {$key}\n");
        return 1;
    }

    /**
     * Drop all tables, views, and types
     * Subcommand: php tame db:wipe
     */
    public function wipe(array $args = [], array $options = []): int
    {
        $this->forceChecker($options);

        // prompt for confirmation before proceeding
        $confirm = $this->confirm('Proceed with db:wipe?');

        // ask once
        if (!$confirm) {
            $this->warning("Command aborted.\n");
            exit(0);
        }

        $tables  = $this->getTables();
        $views   = $this->getViews();
        $types   = $this->getTypes();
        $allItems = array_merge($tables, $views, $types);

        // proceed
        $this->progressBar(function ($report) use ($tables, $views, $types) {
            foreach ($tables as $table) {
                $this->deleteTable($table);
                $report();
            }

            foreach ($views as $view) {
                $this->deleteView($view);
                $report();
            }

            foreach ($types as $type) {
                $this->deleteType($type);
                $report();
            }
        }, count($allItems));

        $this->success("Database wiped successfully.");

        exit(1);
    }

    /**
     * Get all tables in the current database
     */
    protected function getTables(): array
    {
        return $this->conn
                    ->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
                    ->get()
                    ->pluck('tables_in_test')
                    ->toArray();
    }

    /**
     * Get all views in the current database
     */
    protected function getViews(): array
    {
        return $this->conn
                    ->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")
                    ->get()
                    ->toArray();
    }

    /**
     * Get all custom types (if using MySQL, types are not common; for PostgreSQL, see info below)
     */
    protected function getTypes(): array
    {
        // For MySQL, you can skip types since enums etc. are table columns
        // For PostgreSQL, you would query: SELECT typname FROM pg_type WHERE typtype='e';
        return (new Collection([]))->toArray();
    }

    /**
     * Delete a table
     */
    protected function deleteTable(string $table): void
    {
        $this->conn->query("DROP TABLE IF EXISTS `$table`")->exec();
    }

    /**
     * Delete a view
     */
    protected function deleteView(string $view): void
    {
        $this->conn->query("DROP VIEW IF EXISTS `$view`")->exec();
    }

    /**
     * Delete a type (for PostgreSQL only)
     */
    protected function deleteType(string $type): void
    {
        $this->conn->query("DROP TYPE IF EXISTS \"$type\" CASCADE")->exec();
    }
    
}