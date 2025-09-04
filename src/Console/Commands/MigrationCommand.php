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
     * Default entry when running: php tame migrate
     */
    public function handle(array $args = [], array $options = []): int
    {
        echo "Usage examples:\n";
        echo "  php tame migrate\n";
        echo "  php tame migrate:fresh [--seed] [--force] [--database=mysql]\n\n";
        return 0;
    }

    /**
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

        // TODO: implement the actual drop-all + migrate logic
        echo "[demo] Dropping all tables...\n";
        echo "[demo] Running migrations...\n";

        if ($seed) {
            $this->seed($args, $options);
        }

        echo "Migrations completed.\n";
        return 0;
    }

    /**
     * Example flag/subcommand method: --seed
     * Can also be invoked as: php tame migrate:seed (if desired)
     */
    public function seed(array $args = [], array $options = []): int
    {
        echo "[demo] Seeding database...\n";
        // TODO: call your seeder pipeline here
        return 0;
    }

    /**
     * Flag method: --force
     */
    public function force(array $args = [], array $options = []): int
    {
        // Could set internal state or skip confirmations
        return 0;
    }

    /**
     * Option method example: --database=mysql => database(["mysql"]) if you prefer
     */
    public function database(array $args = [], array $options = []): int
    {
        // No-op placeholder to show that options with values are also routed
        return 0;
    }
}