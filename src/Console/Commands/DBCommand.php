<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Database\Constant;
use Tamedevelopers\Database\DBExport;
use Tamedevelopers\Database\DBImport;
use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Database\DBSchemaExport;
use Tamedevelopers\Support\Capsule\CommandHelper;
use Tamedevelopers\Support\Collections\Collection;

class DBCommand extends CommandHelper
{
    
    /**
     * Default entry when running command
     */
    public function handle(array $args = [], array $options = []): mixed
    {
        // Logger::helpHeader("Description:\n");
        Logger::writeln('<yellow>Usage:</yellow>');
        Logger::writeln('  php tame db:seed');
        Logger::writeln('  php tame db:wipe');
        Logger::writeln('  php tame db:import --connection=wocommerce --path=tests/database/orm.sql');
        Logger::writeln('  php tame db:export --connection=wocommerce --as=zip --days=5');
        Logger::writeln('  php tame db:schema --connection= --path= --type=[orm|laravel]');
        Logger::writeln('');

        exit(1);
    }

    /**
     * Seed the database with records
     * Subcommand: php tame db:seed
     */
    public function seed(array $args = [], array $options = []): mixed
    {
        $this->info('No Seed: implementation yet!');
        exit(1);
    }

    /**
     * Generate a migration Schema from a Database using [.sql]
     * Subcommand: php tame db:seed
     */
    public function schema(array $args = [], array $options = []): mixed
    {
        $connection = $this->getOption($options, 'connection');
        $path       = $this->getOption($options, 'path');
        $type       = $this->getOption($options, 'type');

        $import = new DBSchemaExport(
            path: !empty($path) ? base_path($path) : $path, 
            connection: $connection,
            type: $type,
        );

        $this->checkConnection($import->conn);

        $response = null;

        $this->progressBar(function ($report) use ($import, &$response) {
            $response = $import->run();

            $report();
        });

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            exit(0);
        }

        $this->success($response['message']);
        exit(1);
    }

    /**
     * Import a Database [.sql] file into the database
     * Subcommand: php tame db:seed
     */
    public function import(array $args = [], array $options = []): mixed
    {
        $connection = $this->getOption($options, 'connection');
        $path       = $this->getOption($options, 'path');

        $import = new DBImport(
            path: base_path($path), 
            connection: $connection
        );

        $this->checkConnection($import->conn);

        $response = null;

        $this->progressBar(function ($report) use ($import, &$response) {
            $response = $import->run();

            $report();
        });

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            exit(0);
        }

        $this->success($response['message']);
        exit(1);
    }

    /**
     * Export a Database file into [.sql] and convert to <zip|rar>
     * Subcommand: php tame db:export
     */
    public function export(array $args = [], array $options = []): mixed
    {
        $connection = $this->getOption($options, 'connection');
        $as         = $this->getOption($options, 'as');
        $days       = $this->getOption($options, 'days');

        $export = new DBExport(
            saveAsFileType: $as, 
            connection: $connection, 
            retentionDays: (int) $days ?: 7
        );

        $this->checkConnection($export->conn);

        $response = null;

        $this->progressBar(function ($report) use ($export, &$response) {
            $response = $export->run();

            $report();
        });

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            exit(0);
        }

        // path
        $path = empty($response['path']) ? '' : "\n{$response['path']}";

        $this->success("{$response['message']}{$path}");
        exit(1);
    }

    /**
     * Drop all tables, views, and types
     * Subcommand: php tame db:wipe
     */
    public function wipe(array $args = [], array $options = []): mixed
    {
        $this->forceChecker($options);

        // prompt for confirmation before proceeding
        $confirm = $this->confirm('Proceed with db:wipe?');

        // ask once
        if (!$confirm) {
            $this->warning("Command aborted.\n");
            return 0;
        }

        // Determine what to drop based on flags. If any drop-* flags are provided,
        // only drop those categories; otherwise drop all by default.
        $flags = $this->getFlagTypes($options);
        $restrictToFlags = !empty($flags);
        $dropViews  = $restrictToFlags ? in_array('views', $flags, true)  : true;
        $dropTypes  = $restrictToFlags ? in_array('types', $flags, true)  : true;

        $tables = $this->getTables();
        $views  = $dropViews  ? $this->getViews()  : [];
        $types  = $dropTypes  ? $this->getTypes()  : [];

        // Build total for progress bar
        $allItems = array_merge($views, $tables, $types);

        $driver = $this->conn->getConfig()['driver'] ?? null;

        $this->progressBar(function ($report) use ($views, $tables, $types, $driver) {
            // Drop views first (safer if they reference tables)
            foreach ($views as $view) {
                $this->deleteView($view);
                $report();
            }

            // Disable FK checks for MySQL and SQLite while dropping tables
            if (!empty($tables)) {
                if ($driver === 'mysql') {
                    $this->conn->query("SET FOREIGN_KEY_CHECKS = 0")->exec();
                } elseif ($driver === 'sqlite') {
                    $this->conn->query("PRAGMA foreign_keys = OFF")->exec();
                }

                foreach ($tables as $table) {
                    $this->deleteTable($table);
                    $report();
                }

                // Re-enable FK checks
                if ($driver === 'mysql') {
                    $this->conn->query("SET FOREIGN_KEY_CHECKS = 1")->exec();
                } elseif ($driver === 'sqlite') {
                    $this->conn->query("PRAGMA foreign_keys = ON")->exec();
                }
            }

            // Drop custom types last (mainly for PostgreSQL)
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
        // Use information_schema to avoid relying on database-specific column names
        return $this->conn
                    ->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'")
                    ->get()
                    ->pluck('table_name')
                    ->toArray();
    }

    /**
     * Get all views in the current database
     */
    protected function getViews(): array
    {
        // Use information_schema to fetch view names reliably
        return $this->conn
                    ->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'VIEW'")
                    ->get()
                    ->pluck('table_name')
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