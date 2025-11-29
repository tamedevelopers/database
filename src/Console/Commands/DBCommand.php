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
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {
        $this->handleHeader('db');
        Logger::writeln('  db:seed');
        Logger::writeln('  db:wipe');
        Logger::writeln('  db:import --connection=woocommerce --path=tests/database/orm.sql');
        Logger::writeln('  db:export --connection=woocommerce --as=zip --days=5');
        Logger::writeln('  db:schema --connection= --path= --type=[orm|laravel]');
        Logger::writeln('');
    }

    /**
     * Seed the database with records
     * Subcommand: db:seed
     */
    public function seed()
    {
        $this->info('No Seed: implementation yet!');
    }

    /**
     * Generate a migration Schema from a Database using [.sql]
     * Subcommand: db:seed
     */
    public function schema()
    {
        $connection = $this->option('connection');
        $path       = $this->option('path');
        $type       = $this->option('type');

        $schema = new DBSchemaExport(
            path: !empty($path) ? base_path($path) : $path, 
            connection: $connection,
            type: $type,
        );
        
        $this->checkConnection($schema->conn);
        $response = ['status' => Constant::STATUS_400, 'message' => ''];

        if ($this->isConsole()) {
            $this->progressBar(function ($report) use ($schema, &$response) {
                $response = $schema->run();
                $report();
            });
        } else{
            $response = $schema->run();
        }

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            return 0;
        }

        $this->success($response['message']);
        return 1;
    }

    /**
     * Import a Database [.sql] file into the database
     * Subcommand: db:seed
     */
    public function import()
    {
        $connection = $this->option('connection');
        $path       = $this->option('path');

        $import = new DBImport(
            path: base_path($path), 
            connection: $connection
        );

        $this->checkConnection($import->conn);
        $response = ['status' => Constant::STATUS_400, 'message' => ''];

        if ($this->isConsole()) {
            $this->progressBar(function ($report) use ($import, &$response) {
                $response = $import->run();
                $report();
            });
        } else{
            $response = $import->run();
        }

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            return 0;
        }

        $this->success($response['message']);
        return 1;
    }

    /**
     * Export a Database file into [.sql] and convert to <zip|rar>
     * Subcommand: db:export
     */
    public function export()
    {
        $connection = $this->option('connection');
        $as         = $this->option('as');
        $days       = $this->option('days');

        $export = new DBExport(
            saveAsFileType: $as, 
            connection: $connection, 
            retentionDays: (int) $days ?: 5
        );

        $this->checkConnection($export->conn);
        $response = ['status' => Constant::STATUS_400, 'message' => ''];

        if ($this->isConsole()) {
            $this->progressBar(function ($report) use ($export, &$response) {
                $response = $export->run();
                $report();
            });
        } else{
            $response = $export->run();
        }

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            return 0;
        }

        // path
        $path = empty($response['path']) ? '' : "\n{$response['path']}";

        $this->success("{$response['message']}{$path}");
        return 1;
    }

    /**
     * Drop all tables, views, and types (--drop-types --drop-views)
     * Subcommand: db:wipe
     */
    public function wipe()
    {
        $force = $this->force();
        $response = $this->option('response');

        $this->forceChecker();

        // only prompt for confirmation when `response != 0`
        if(!($response == '0')){
            // prompt for confirmation before proceeding
            $confirm = $this->confirm('Proceed with db:wipe?');
    
            // ask once
            if (!$confirm && $this->isConsole()) {
                $this->warning("Command aborted.");
                return 0;
            }
        }
        
        $tables = $this->getTables();
        $views  = $this->getViews();
        $types  = $this->getTypes();

        // Build total for progress bar
        $allItems = array_merge($views, $tables, $types);
        
        // only display response when needed
        if($this->shouldResponseReturn($response) && $this->isConsole()){
            $this->progressBar(function ($report) use ($views, $tables, $types) {
                $this->processWipeData($views, $tables, $types, $report);
            }, count($allItems));
            
            $this->success("Database wiped successfully.");
        } else{
            $this->processWipeData($views, $tables, $types);
        }

        return 1;
    }
        
    /**
     * Process Wipe Data
     *
     * @param  mixed $views
     * @param  mixed $tables
     * @param  mixed $types
     * @param  Callable|null $report
     * @return void
     */
    protected function processWipeData($views, $tables, $types, $report = null)
    {
        $driver = $this->conn->getConfig()['driver'] ?? null;

        // Drop views first (safer if they reference tables)
        foreach ($views as $view) {
            $this->deleteView($view);
            if(is_callable($report))
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
                if(is_callable($report))
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
            if(is_callable($report))
                $report();
        }
    }

    /**
     * If response should be returned
     *
     * @param string|null $response
     * @return bool
     */
    protected function shouldResponseReturn($response)
    {
        return (is_null($response) || $response != '0');
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