<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Tame;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Support\Capsule\Logger;
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
        Logger::helpHeader('<yellow>Usage:</yellow>');
        Logger::writeln('  php tame scaffold:run --force');
        Logger::writeln('');
    }

    /**
     * App scaffolding
     * Subcommand: php tame scaffold:run
     */
    public function run()
    {
        // if app is running inside of a framework
        $frameworkChecker = (new Tame)->isAppFramework();

        // force scaffold command only on local environment
        $force = $this->force();

        // Check for framework mode
        if($frameworkChecker && !$force){
            $this->warning("Sorry! This command can't be run in a framework.");
            return 0;
        }

        // prompt for confirmation before proceeding
        $confirm = $this->confirm('Proceed with Scalfolding the application?');

        // ask once
        if (!$confirm && $this->isConsole()) {
            $this->warning("Command aborted.");
            return 0;
        }
        
        // scaffolding database manager
        AutoLoader::start();
        
        $this->info("App scaffolding Manager has been successfully runned!");
        return 1;
    }

}