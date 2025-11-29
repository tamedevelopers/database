<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Tame;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Database\Migrations\Migration;
use Tamedevelopers\Support\Capsule\CommandHelper;


class SessionCommand extends CommandHelper
{

    /**
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {
        $this->handleHeader('session');
        Logger::writeln(' session:clean');
        Logger::writeln(' session:table');
        Logger::writeln(' session:config');
        Logger::writeln('');
    }

    /**
     * Clean expired session files
     * Subcommand: session:clean
     */
    public function clean()
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

    /**
     * Create a Session configuration file
     * Subcommand: session:config
     */
    public function config()
    {
        $realPath = realpath(__DIR__ . '/../../');
        $autoloader = new AutoLoader();

        $paths = $autoloader->getPathsData($realPath);

        $sessionPath = $paths['session']['path'];

        // Check if session config file already exists
        if(File::exists($sessionPath)) {
            $this->error(sprintf(
                "Session configuration file already exists at: [%s]", 
                config_path('session.php')
            ));
            return 0;
        }

        $autoloader->createSession($paths);

        $this->info(sprintf(
            "Session configuration file created at: [%s]", 
            config_path('session.php')
        ));
        return 1;
    }

    /**
     * Create a migration for the session database table
     * Subcommand: session:table
     */
    public function table()
    {
        $migration = new Migration();

        $response = $migration->create('sessions', 'sessions');

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            return 0;
        }
        
        $this->info($response['message']);
        return 1;
    }

}