<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\Constant;
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
    public function fresh(array $args = [], array $options = [])
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
        exit(1);
    }

    /**
     * Reset and re-run all migrations
     */
    public function refresh(array $args = [], array $options = [])
    {
        Artisan::call('migrate:fresh --force --drop-types --drop-views');
    }

    /**
     * Show the status of each migration
     */
    public function status(array $args = [], array $options = [])
    {
        echo "[demo] Seeding database...\n";
        // TODO: call your seeder pipeline here
        return 0;
    }

    /**
     * Rollback all database migrations
     */
    public function reset(array $args = [], array $options = [])
    {
        // No-op placeholder to show that options with values are also routed
        return 0;
    }

}