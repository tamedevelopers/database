<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Support\Tame;
use Tamedevelopers\Database\AutoLoader;
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
        // if app is running inside of a framework
        $frameworkChecker = (new Tame)->isAppFramework();

        // force scaffold command only on local environment
        $force = $this->force();

        // Check for framework mode
        if(!$frameworkChecker && !$force){
            $this->warning("Sorry! This command can't be run in a framework.");
            return;
        }

        // prompt for confirmation before proceeding
        $confirm = $this->confirm('Proceed with Scalfolding the application?');

        // ask once
        if (!$confirm) {
            $this->warning("Command aborted.");
            return;
        }
        
        // scalffolding database manager
        AutoLoader::start();
        
        $this->info("App scalffolding Manager has been successfully runned!");
    }

}