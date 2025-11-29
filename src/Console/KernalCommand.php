<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console;

use Tamedevelopers\Support\Capsule\Artisan;
use Tamedevelopers\Database\Console\Commands\DBCommand;
use Tamedevelopers\Database\Console\Commands\KeyCommand;
use Tamedevelopers\Database\Console\Commands\MakeCommand;
use Tamedevelopers\Database\Console\Commands\SessionCommand;
use Tamedevelopers\Support\Capsule\CommandProviderInterface;
use Tamedevelopers\Database\Console\Commands\ScaffoldCommand;
use Tamedevelopers\Database\Console\Commands\MigrationCommand;


class KernalCommand implements CommandProviderInterface
{
    /** @inheritDoc */
    public function register(Artisan $artisan)  :void
    {
        // Register built-in commands with class instances (enables subcommands and flags-as-methods)
        $artisan->register('make', new MakeCommand());
        $artisan->register('scaffold', new ScaffoldCommand(), 'Generate default scaffolding');
        $artisan->register('session', new SessionCommand(), 'Run session migrations and configurations');
        $artisan->register('migrate', new MigrationCommand(), 'Run database migrations');
        $artisan->register('key', new KeyCommand(), 'Set or manage the application key');
        $artisan->register('db', new DBCommand(), 'Start a new database CLI session');
    }

}