<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Support\Capsule\CommandHelper;


class ScaffoldCommand extends CommandHelper
{

    /**
     * Default entry when running command
     */
    public function handle(array $args = [], array $options = [])
    {

        dd(
            $args,
            $options
        );
        // Ensure env and logger are booted
        Env::boot();
        Env::loadOrFail();

        // Example: ensure connection booted (if your manager requires it)
        // Adjust to your initialization flow if different
        $db = new DatabaseManager();
        
        
    }

}