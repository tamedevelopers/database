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
    public function migration()
    {
        // Could set internal state or skip confirmations
        $table  = $this->arguments(0);
        $create = $this->option('create');

        // if options is valid and came
        if(!empty($create)){
            $table = $create;
        }

        // if no table file name
        if(empty($table) && $this->isConsole()){
            $table = $this->ask("\nWhat should the migration be named?");
        }
        
        if(empty($table)){
            return 0;
        }

        // if create flag come, then we use it as table name
        if(!empty($create)){
            $table = $create;
        } else{
            $table = $this->extractTableName($table);
        }

        $migration = new Migration();

        $response = $migration->create($table);

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            if($this->isConsole()){
                exit(0);
            } else{
                return 0;
            }
        }

        $this->info($response['message']);
        return 1;
    }

}