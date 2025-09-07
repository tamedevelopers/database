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
        echo "Usage:\n";
        echo "  php tame key:generate\n\n";
    }

    /**
     * Generate and display a new application key
     * Subcommand: php tame key:generate
     */
    public function generate()
    {
        $key = Manager::regenerate();
        if ( !$key) {
            Logger::error('Failed to generate the application key.');
            return 0;
        }

        Logger::success("Application key generated: {$key}\n");
        return 1;
    }
    
}