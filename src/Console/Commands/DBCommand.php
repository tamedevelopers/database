<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Database\Console\Commands\CommandHelper;

class DBCommand 
{   
    use CommandHelper;
    
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
        // proceed
        $this->progressBar(function ($step) {
            dd(
                $step
            );
        });

        $this->success("Database wiped successfully.");
        exit(1);
    }
    
}