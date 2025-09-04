<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Database\Console\Commands\Traits\CommandTrait;


class KeyCommand
{   
    use CommandTrait;

    /**
     * Handle the command
     * @param array $args
     * @return int exit code
     */
    public function handle(array $args)
    {
        // Basic args parsing: --name=ModelName
        $name = $this->getOption($args, 'name') ?? 'example';

        // Ensure env and logger are booted
        Env::boot();
        Env::loadOrFail();

        // Example: ensure connection booted (if your manager requires it)
        // Adjust to your initialization flow if different
        $db = new DatabaseManager();

        dd(
            'sss'
        );
    }

}