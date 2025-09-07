<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Database\Migrations\Migration;
use Tamedevelopers\Support\Capsule\CommandHelper;


class MakeCommand extends CommandHelper
{   
    /**
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {
        Logger::helpHeader('<yellow>Usage:</yellow>');
        Logger::writeln('  php tame make:migration admins');
        Logger::writeln('  php tame make:migration create_users_table --create=users');
        Logger::writeln('');
    }

    /**
     * Create a new migration file
     */
    public function migration(array $args = [], array $options = [])
    {
        // Could set internal state or skip confirmations
        $table  = $args[0] ?? null;
        $create = $this->option('create');

        // if options is valid and came
        if(!empty($create)){
            $table = $create;
        }

        // if no table file name
        if(empty($table)){
            $table = $this->ask("\nWhat should the migration be named?");
        }

        $table = $this->extractTableName($table);

        $migration = new Migration();

        $response = $migration->create($table);

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            exit(0);
        }

        $this->info($response['message']);
        exit(1);
    }

}