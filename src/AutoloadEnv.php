<?php

declare(strict_types=1);

namespace UltimateOrmDatabase;
    
use UltimateOrmDatabase\Methods\OrmDotEnv;


class AutoloadEnv{

    /**
     * Star env configuration
     * 
     * @param array $option 
     * path \Path to .env file
     * bg \dump background color (default | main | dark | red | blue)
     * 
     * @return void\start
     */
    static public function start(?array $option = [])
    {
        /*
        |--------------------------------------------------------------------------
        | Create default path and bg for errors
        |--------------------------------------------------------------------------
        */
        $default = [
            'path'  => $option['path']  ?? null,
            'bg'    => $option['bg']    ?? 'default',
        ];
        

        /*
        |--------------------------------------------------------------------------
        | Instance of class
        |--------------------------------------------------------------------------
        */
        $ormDotEnv = new OrmDotEnv($default['path']);
        

        /*
        |--------------------------------------------------------------------------
        | Create a sample .env file if not exist in project
        |--------------------------------------------------------------------------
        */
        $ormDotEnv::createOrIgnore();
        

        /*
        |--------------------------------------------------------------------------
        | Load environment file (associated to database)
        |--------------------------------------------------------------------------
        | This will automatically6 setup our database configuration if found 
        |
        */
        $loader = $ormDotEnv::loadOrFail();

        
        /*
        |--------------------------------------------------------------------------
        | Defining background color for var dump
        |--------------------------------------------------------------------------
        | default | main | dark | red | blue
        */
        $ormDotEnv->{'bg'} = $default['bg'];


        /*
        |--------------------------------------------------------------------------
        | Update ENV variable
        |--------------------------------------------------------------------------
        | Here we do not want to temper with the environment file always
        | - Since this path will always run at every application call
        | - We only will update APP_DEBUG_BG if env path is set and 
        | - If the APP_DEBUG_BG is empty
        |
        */
        if(isset($_ENV['APP_DEBUG_BG'])){
            if(empty($_ENV['APP_DEBUG_BG'])){
                $ormDotEnv::updateENV('APP_DEBUG_BG', $ormDotEnv->{'bg'}, false);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Check If There was an error getting the environment file
        |--------------------------------------------------------------------------
        |
        | If there's an error then exit code from running, as this will cause 
        | Error on using the Database model
        |
        */
        if($loader['response'] != 200){
            /**
             * Setting application to use the dump error handling
             */
            $ormDotEnv->dump_final = false;

            /**
             * Dump error message
             */
            $ormDotEnv->dump( $loader['message'] );
            die(1);
        }
        
        /*
        |--------------------------------------------------------------------------
        | Storing data into a Constant once everything is successful
        |--------------------------------------------------------------------------
        | We can now use on anywhere on our application 
        | Mostly to get our defined .env root Path
        |
        | ORM_ENV_CLASS['path'] -> return .env root Path
        */
        if ( ! defined('ORM_ENV_CLASS') ) {
            define('ORM_ENV_CLASS', $loader);
        }
    }
    
}