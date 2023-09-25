<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Capsule;

use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Support\Capsule\FileCache;
use Tamedevelopers\Database\Capsule\DebugManager;

class AppManager{

    /**
     * Re-generate a new app KEY
     * 
     * @return void
     */
    public static function regenerate()
    {
        Manager::regenerate();
    }

    /**
     * Starting our Application
     * 
     * @return void
     */
    public static function bootLoader()
    {
        /*
        |--------------------------------------------------------------------------
        | Auto start debug manager
        |--------------------------------------------------------------------------
        */
        DebugManager::boot();

        /*
        |--------------------------------------------------------------------------
        | To Disable/Enable Error Resporting
        | Update the .env and set `APP_DEBUG` to true|false
        | 
        | Error report is saved into the below path to file
        | path => \storage\logs\orm.log
        |--------------------------------------------------------------------------
        */
        Env::bootLogger();

        /*
        |--------------------------------------------------------------------------
        | Mainly for Database Connections Cache
        | Here we defined cache path for easy connection storage
        |--------------------------------------------------------------------------
        */
        FileCache::setCachePath("cache"); 

        /*
        |--------------------------------------------------------------------------
        | Start env configuration
        | You can configura your pagination text data here if you like
        |--------------------------------------------------------------------------
        */
        AutoLoader::start();

        /*
        |--------------------------------------------------------------------------
        | Default connection driver is `mysql`
        | use DB::connection() \to connection to other connection instance
        |--------------------------------------------------------------------------
        */
        DB::connection();
    }

}