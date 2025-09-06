<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Support\Capsule\CommandHelper;

class KeyCommand extends CommandHelper
{   
    /**
     * Default entry when running command
     */
    public function handle(array $args = [], array $options = []): int
    {
        echo "Usage:\n";
        echo "  php tame key:generate\n\n";
        return 0;
    }

    /**
     * Generate and display a new application key
     * Subcommand: php tame key:generate
     */
    public function generate(array $args = [], array $options = []): int
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