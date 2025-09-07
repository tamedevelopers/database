<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Capsule\Artisan;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Database\Migrations\Migration;
use Tamedevelopers\Support\Capsule\CommandHelper;


class MigrationCommand extends CommandHelper
{

    /**
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {
        echo "Usage examples:\n";
        echo "  php tame migrate:fresh --seed --force\n";
        echo "  php tame migrate:refresh --seed --force\n";
        echo "  php tame migrate:status\n";
        echo "  php tame migrate:reset\n";
    }

    /**
     * Drop all tables and re-run all migrations
     * Subcommand: php tame migrate:fresh
     */
    public function fresh()
    {
        $force = $this->option('force');
        $seed  = $this->option('seed');

        if($force){
            Artisan::call('db:wipe --force --drop-types --drop-views --response=0');
        }

        $migration = new Migration();

        $response = $migration->run();

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            exit(0);
        }

        $this->info($response['message']);
    }

    /**
     * Reset and re-run all migrations
     */
    public function refresh()
    {
        Artisan::call('migrate:fresh --force --drop-types --drop-views');
    }

    /**
     * Show the status of each migration
     */
    public function status()
    {
        // Resolve migrations directory
        $migrationsDir = Env::getServers('server') . "database/migrations/";

        if (!is_dir($migrationsDir)) {
            $this->warning("Migrations directory not found: {$migrationsDir}");
            return 0;
        }

        // Connect to DB and validate connection
        $conn = DatabaseManager::connection();
        $this->checkConnection($conn);

        // Gather migration files
        $files = array_values(array_filter(scandir($migrationsDir), static function ($f) use ($migrationsDir) {
            return is_file($migrationsDir . $f) && str_ends_with($f, '.php');
        }));
        sort($files);

        if (empty($files)) {
            $this->info("No migration files found in: {$migrationsDir}");
            return 0;
        }

        $this->info("Migration status:");
        foreach ($files as $file) {
            $fullPath = $migrationsDir . $file;

            // Try to detect created table from file content first
            $table = null;
            $content = File::get($fullPath) ?: '';
            if (preg_match("/Schema::create\\(['\"]([a-zA-Z0-9_]+)['\"]/", $content, $m)) {
                $table = $m[1];
            } elseif (preg_match('/create_(.+)_table\\.php$/', $file, $m)) {
                $table = $m[1];
            }

            if (!$table) {
                $this->warning("Unable to detect table for migration: {$file}");
                continue;
            }

            $exists = (bool) $conn->tableExists($table);
            if ($exists) {
                $this->success(sprintf("%-30s %s", $table, '[OK]'));
            } else {
                $this->warning(sprintf("%-30s %s", $table, '[MISSING]'));
            }
        }

        return 0;
    }

    /**
     * Rollback all database migrations
     */
    public function reset()
    {
        // Require --force in production environments
        $this->forceChecker();

        $force = (bool) $this->option('force');

        $migration = new Migration();
        $response = $migration->drop($force);

        if ($response['status'] != Constant::STATUS_200) {
            $this->error($response['message']);
            return 0;
        }

        $this->info($response['message']);
        return 0;
    }

}