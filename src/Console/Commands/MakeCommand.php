<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Capsule\CommandHelper;


class MakeCommand extends CommandHelper
{   
    /**
     * Default entry when running command
     */
    public function handle(array $args = [], array $options = []): int
    {
        echo "Usage examples:\n";
        echo "  php tame make\n";
        echo "  php tame make:migration [name]\n\n";
        return 0;
    }

    /**
     * Create a new migration file
     */
    public function migration(array $args = [], array $options = []): int
    {
        // Could set internal state or skip confirmations
        return 0;
    }

}