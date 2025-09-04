<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Database\Console\Commands\Traits\CommandTrait;

class MigrationCommand
{   
    use CommandTrait;

    /**
     * Default entry when running command
     */
    public function handle(array $args = [], array $options = []): int
    {
        echo "Usage examples:\n";
        echo "  php tame migrate\n";
        echo "  php tame migrate:fresh [--seed] [--force] [--database=mysql]\n\n";
        return 0;
    }

    /**
     * Drop all tables and re-run all migrations
     * Subcommand: php tame migrate:fresh
     */
    public function fresh(array $args = [], array $options = []): int
    {
        Env::boot();
        Env::loadOrFail();

        $db = new DatabaseManager();

        $database = $options['database'] ?? null;
        $force = isset($options['force']) && $options['force'] !== false;
        $seed = isset($options['seed']) && $options['seed'] !== false;

        echo "Running migrations: FRESH" . ($database ? " on connection '{$database}'" : "") . "\n";
        if (!$force) {
            echo "Add --force to bypass confirmations in production.\n";
        }


        dd(
            'ss'
        );

        

        // TODO: implement the actual drop-all + migrate logic
        echo "[demo] Dropping all tables...\n";
        echo "[demo] Running migrations...\n";

        if ($seed) {
            // $this->seed($args, $options);
        }

        echo "Migrations completed.\n";
        return 0;
    }

    /**
     * Show the status of each migration
     */
    public function status(array $args = [], array $options = []): int
    {
        echo "[demo] Seeding database...\n";
        // TODO: call your seeder pipeline here
        return 0;
    }

    /**
     * Reset and re-run all migrations
     */
    public function refresh(array $args = [], array $options = []): int
    {
        // Could set internal state or skip confirmations
        return 0;
    }

    /**
     * Rollback all database migrations
     */
    public function reset(array $args = [], array $options = []): int
    {
        // No-op placeholder to show that options with values are also routed
        return 0;
    }
}