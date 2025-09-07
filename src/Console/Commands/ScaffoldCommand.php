<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Support\Capsule\CommandHelper;


class ScaffoldCommand extends CommandHelper
{

    /**
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {

        dd(
            'scaffold command'
        );
        // Ensure env and logger are booted
        Env::boot();
        Env::loadOrFail();

        // Example: ensure connection booted (if your manager requires it)
        // Adjust to your initialization flow if different
        $db = new DatabaseManager();
        
        
    }

}