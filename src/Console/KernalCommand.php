<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console;


use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Capsule\Artisan;
use Tamedevelopers\Database\Console\Commands\DBCommand;
use Tamedevelopers\Database\Console\Commands\KeyCommand;
use Tamedevelopers\Database\Console\Commands\MakeCommand;
use Tamedevelopers\Support\Capsule\CommandProviderInterface;
use Tamedevelopers\Database\Console\Commands\ScaffoldCommand;
use Tamedevelopers\Database\Console\Commands\MigrationCommand;



class KernalCommand implements CommandProviderInterface
{

    /** @inheritDoc */
    public function register(Artisan $artisan)  :void
    {
        $conn = DB::connection();

        // Register built-in commands with class instances (enables subcommands and flags-as-methods)
        $artisan->register('scaffold', new ScaffoldCommand($conn), 'Generate default scaffolding');
        $artisan->register('migrate', new MigrationCommand($conn), 'Run database migrations');
        $artisan->register('make', new MakeCommand($conn), 'Make Artisans');
        $artisan->register('key', new KeyCommand($conn), 'Set or manage the application key');
        $artisan->register('db', new DBCommand($conn), 'Start a new database CLI session');
    }
}