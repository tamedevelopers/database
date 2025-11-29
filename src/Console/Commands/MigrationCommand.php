<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Capsule\Logger;
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
        $this->handleHeader('migrate');
        Logger::writeln('  migrate:fresh --seed --force');
        Logger::writeln('  migrate:refresh');
        Logger::writeln('  migrate:status');
        Logger::writeln('  migrate:reset');
        Logger::writeln('');
    }

    /**
     * Drop all tables and re-run all migrations
     * Subcommand: migrate:fresh
     */
    public function fresh()
    {
        $force = $this->force();
        $seed  = $this->option('seed');

        $this->forceChecker();

        if($force){
            Artisan::call('db:wipe --force --drop-types --drop-views --response=0');
        }

        $migration = new Migration();
        $response = $migration->run();

        if(!$this->dbConnect($response)){
            $this->error($response['message']);
            if($this->isConsole()){
                exit(0);
            } else{
                return 0;
            }
        }

        $this->info($response['message']);
        return 1;
    }

    /**
     * Reset and re-run all migrations
     */
    public function refresh()
    {
        return Artisan::call('migrate:fresh --force');
    }

    /**
     * Rollback all database migrations
     */
    public function reset()
    {
        $this->forceChecker();

        $force = $this->force(); 

        $migration = new Migration();
        $response = $migration->drop($force);

        if (!$this->dbConnect($response)) {
            $this->error($response['message']);
            return 0;
        }

        $this->info($response['message']);
        return 1;
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

        // Build a single table output
        $rows = [];
        foreach ($files as $file) {
            $fullPath = $migrationsDir . $file;

            // Detect created table from file content or filename
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
            $rows[] = [$file, $table, $exists ? 'OK' : 'MISSING'];
        }

        if (empty($rows)) {
            $this->info("No detectable migration tables.");
            return 0;
        }

        // Compute column widths
        $headers = ['Migration', 'Table', 'Status'];
        $w0 = strlen($headers[0]);
        $w1 = strlen($headers[1]);
        $w2 = strlen($headers[2]);
        foreach ($rows as [$f, $t, $s]) {
            $w0 = max($w0, strlen((string)$f));
            $w1 = max($w1, strlen((string)$t));
            $w2 = max($w2, strlen((string)$s));
        }

        // Helpers to draw lines
        $sep = '+' . str_repeat('-', $w0 + 2) . '+' . str_repeat('-', $w1 + 2) . '+' . str_repeat('-', $w2 + 2) . '+';
        $rowFn = static function ($a, $b, $c) use ($w0, $w1, $w2) {
            return sprintf('| %-' . $w0 . 's | %-' . $w1 . 's | %-' . $w2 . 's |', $a, $b, $c);
        };

        // Render table
        Logger::writeln($sep);
        Logger::writeln($rowFn($headers[0], $headers[1], $headers[2]));
        Logger::writeln($sep);
        foreach ($rows as [$f, $t, $s]) {
            Logger::writeln($rowFn($f, $t, $s));
        }
        Logger::writeln($sep);

        return 1;
    }

    /** Check Database connection */
    private function dbConnect($response): bool
    {
        $status = $response['status'] ?? null;

        return $status == Constant::STATUS_200;
    }
}