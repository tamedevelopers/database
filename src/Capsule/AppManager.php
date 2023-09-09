<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

use builder\Database\DB;
use builder\Database\Env;
use builder\Database\AutoLoader;
use builder\Database\Capsule\FileCache;
use builder\Database\Capsule\DebugManager;

class AppManager{
    
    /**
     * Sample copy of env file
     * 
     * @return string
     */
    public static function envDummy()
    {
        return preg_replace("/^[ \t]+|[ \t]+$/m", "", 'APP_NAME="PHP ORM Database"
            APP_ENV=local
            APP_KEY='. self::generate() .'
            APP_DEBUG=true
            SITE_EMAIL=
            
            DB_CONNECTION=mysql
            DB_HOST="127.0.0.1"
            DB_PORT=3306
            DB_USERNAME="root"
            DB_PASSWORD=
            DB_DATABASE=

            DB_CHARSET=utf8mb4
            DB_COLLATION=utf8mb4_general_ci

            MAIL_MAILER=smtp
            MAIL_HOST=
            MAIL_PORT=465
            MAIL_USERNAME=
            MAIL_PASSWORD=
            MAIL_ENCRYPTION=tls
            MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
            MAIL_FROM_NAME="${APP_NAME}"

            AWS_ACCESS_KEY_ID=
            AWS_SECRET_ACCESS_KEY=
            AWS_DEFAULT_REGION=us-east-1
            AWS_BUCKET=
            AWS_USE_PATH_STYLE_ENDPOINT=false

            PUSHER_APP_ID=
            PUSHER_APP_KEY=
            PUSHER_APP_SECRET=
            PUSHER_HOST=
            PUSHER_PORT=443
            PUSHER_SCHEME=https
            PUSHER_APP_CLUSTER=mt1
            
            APP_DEVELOPER=
            APP_DEVELOPER_EMAIL=
        ');
    }

    /**
     * Generates an app KEY
     * 
     * @return string
     */
    private static function generate($length = 32)
    {
        $randomBytes = random_bytes($length);
        $appKey = 'base64:' . rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
        $appKey = str_replace('+', '-', $appKey);
        $appKey = str_replace('/', '_', $appKey);

        // Generate a random position to insert '/'
        $randomPosition = random_int(0, strlen($appKey));
        $appKey         = substr_replace($appKey, '/', $randomPosition, 0);

        $appKey .= '=';

        return $appKey;
    }

    /**
     * Re-generate a new app KEY
     * 
     * @return void
     */
    public static function regenerate()
    {
        Env::updateENV('APP_KEY', self::generate(), false);
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
        env_orm()->bootLogger();

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