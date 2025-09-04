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
    public static function postInstall()
    {
        self::publishDefaults();
    }

    /**
     * Run after composer update
     */
    public static function postUpdate()
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

                // create for [tame] 
                self::createTameBash($paths);

                Logger::success("Tamedevelopers-Dummy data has been created automatically!\n\nUsage: \nphp tame <command> [options]\n\n");
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
        $present = [false];

        // create for database 
        if(!File::exists($paths['database']['path'])){
            $present[] = true;
        }

        // create for tame bash script 
        if(!File::exists($paths['tame']['path'])){
            $present[] = true;
        }

        // Check if all elements in $present are false
        $allFalse = empty(array_filter($present));
        
        // All elements in $present are false
        if ($allFalse) {
            return false;
        } 

        return true;
    }

}
