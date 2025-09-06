<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Database\Constant;
use Tamedevelopers\Database\Migrations\Migration;
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
        echo "  php tame make:migration [name] --create=users\n\n";
        return 0;
    }

    /**
     * Create a new migration file
     */
    public function migration(array $args = [], array $options = []): int
    {
        // Could set internal state or skip confirmations
        $table  = $args[0] ?? null;
        $create = $this->getOption($options, 'create');

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

        $response = $migration::create($table);

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            exit(0);
        }

        $this->info($response['message']);
        exit(1);
    }

}