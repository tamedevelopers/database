<?php

declare(strict_types=1);

namespace UltimateOrmDatabase;

use UltimateOrmDatabase\Methods\OrmDotEnv;

class AutoloadEnv{

    /**
     * Star env configuration
     * 
     * @param array $options 
     * path \Path to .env file
     * bg \dump background color (default | main | dark | red | blue)
     * 
     * @return void\start
     */
    static public function start(?array $options = [])
    {
        /*
        |--------------------------------------------------------------------------
        | Create default path and bg for errors
        |--------------------------------------------------------------------------
        */
        $default = [
            'path'      => $options['path']             ?? null,
            'bg'        => $options['bg']               ?? 'default',
            'allow'     => $options['allow']            ?? 'disallow',
            'class'     => $options['class']            ?? null,
            'view'      => in_array($options['view']    ?? null, ['bootstrap' => 'bootstrap', 'simple' => 'simple']) ? $options['view'] : 'bootstrap',
            'first'     => $options['first']            ?? 'First',
            'last'      => $options['last']             ?? 'Last',
            'next'      => $options['next']             ?? 'Next',
            'prev'      => $options['prev']             ?? 'Prev',
            'span'      => $options['span']             ?? 'pagination-highlight',
            'showing'   => $options['showing']          ?? 'Showing',
            'to'        => $options['to']               ?? 'to',
            'of'        => $options['of']               ?? 'of',
            'results'   => $options['results']          ?? 'results',
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
        | Saving any configuration passed to the env handler into a global varriable
        |--------------------------------------------------------------------------
        */
        $paginationDefault          = $default;
        $paginationDefault['path']  = $loader;
        
        /*
        |--------------------------------------------------------------------------
        | Storing data into a Constant once everything is successful
        |--------------------------------------------------------------------------
        | We can now use on anywhere on our application 
        | Mostly to get our defined .env root Path
        |
        | APP_ORM_DOT_ENV['path'] -> return array of data containing .env path
        */

        if ( ! defined('APP_ORM_DOT_ENV') ) {
            define('APP_ORM_DOT_ENV', $paginationDefault);
        }
    }
    
}