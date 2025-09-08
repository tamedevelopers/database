<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Support\Capsule\CommandHelper;

class KeyCommand extends CommandHelper
{   
    /**
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {
        Logger::helpHeader('<yellow>Usage:</yellow>');
        Logger::writeln('  php tame key:generate');
        Logger::writeln('');
    }

    /**
     * Generate and display a new application key
     * Subcommand: php tame key:generate
     */
    public function generate()
    {
        $key = Manager::regenerate();
        if ( !$key) {
            $this->error('Failed to generate the application key.');
            return;
        }

        $this->success("Application key generated: {$key}");
    }
    
}