<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;


use Tamedevelopers\Support\Tame;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Database\Traits\AutoLoaderTrait;

class Installer
{
    use AutoLoaderTrait;

    /**
     * Run after composer require/install
     */
    public static function install()
    {
        self::publishDefaults();
    }

    /**
     * Run after composer update
     */
    public static function update()
    {
        self::publishDefaults();
    }

    /**
     * Dump default files into the user project root
     */
    protected static function publishDefaults()
    {
        // if app is running inside of a framework
        $frameworkChecker = (new Tame)->isAppFramework();

        // if application is not a framework, 
        // then we can start dupping default needed files
        if(! $frameworkChecker){
            // dummy paths to be created 
            $paths = self::getPathsData(realpath(__DIR__));

            // only create when files are not present
            if(self::isDummyNotPresent_InSelf($paths)){

                // create for database 
                self::createDatabase($paths);

                Logger::success("[config/database.php] has been imported sucessfully!\n");
            }
        }
    }

    /**
     * Check if dummy data is present
     * 
     * @return bool
     */
    private static function isDummyNotPresent_InSelf($paths)
    {
        return !File::exists($paths['database']['path']);
    }

}
