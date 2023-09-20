<?php

declare(strict_types=1);

namespace builder\Database;

use builder\Database\Env;
use builder\Database\Traits\AutoLoaderTrait;
use builder\Database\Schema\Pagination\PaginatorAsset;

class AutoLoader{

    use AutoLoaderTrait;

    protected static $default;

    /**
     * Star env configuration
     * 
     * @param string $custom_path 
     * path \Path to .env file
     * - [optional] path \By default we use project root path
     * 
     * @return void
     */
    public static function start(?string $custom_path = null)
    {
        /*
        |--------------------------------------------------------------------------
        | Instance of class
        |--------------------------------------------------------------------------
        */
        $Env = new Env($custom_path);
        
        /*
        |--------------------------------------------------------------------------
        | Create a sample .env file if not exist in project
        |--------------------------------------------------------------------------
        */
        $Env::createOrIgnore();
        
        /*
        |--------------------------------------------------------------------------
        | Load environment file (associated to database)
        |--------------------------------------------------------------------------
        | This will automatically6 setup our database configuration if found 
        |
        */
        $loader = $Env::loadOrFail();
        
        /*
        |--------------------------------------------------------------------------
        | Check If There was an error getting the environment file
        |--------------------------------------------------------------------------
        |
        | If there's an error then exit code from running, as this will cause 
        | Error on using the Database model
        |
        */
        if($loader['status'] != 200){
            /**
             * Dump error message
             */
            $Env->dump( $loader['message'] );
            die(1);
        }
        
        /*
        |--------------------------------------------------------------------------
        | Storing data into a Constant once everything is successful
        |--------------------------------------------------------------------------
        | We can now use on anywhere on our application 
        | Mostly to get our defined .env root Path
        |
        | DOT_ENV_CONNECTION['env_path'] -> return array of data containing .env path
        */
        if ( ! defined('DOT_ENV_CONNECTION') ) {
            define('DOT_ENV_CONNECTION', array_merge([
                'status'    => $loader['status'],
                'env_path'  => $loader['path'],
                'message'   => $loader['message'],
            ], $Env->getServers()));
        }
        
        /*
        |--------------------------------------------------------------------------
        | Automatically create dummy files
        |--------------------------------------------------------------------------
        */
        self::createDummy(realpath(__DIR__));
    }

    /**
     * Configura pagination data
     * 
     * @param array $options
     * - [optional]
     * 
     * @return void
     */
    public static function configPagination(?array $options = [])
    {
        /*
        |--------------------------------------------------------------------------
        | Create default path and bg for errors
        |--------------------------------------------------------------------------
        */
        $text       = PaginatorAsset::texts();
        $getViews   = PaginatorAsset::views();
        
        $default = array_merge([
            'allow'     => 'disallow',
            'class'     => null,
            'view'      => null,
            'first'     => $text['first'],
            'last'      => $text['last'],
            'next'      => $text['next'],
            'prev'      => $text['prev'],
            'span'      => $text['span'],
            'showing'   => $text['showing'],
            'of'        => $text['of'],
            'results'   => $text['results'],
            'buttons'   => $text['buttons'],
        ], $options);

        // get actual view
        $default['view'] = in_array($default['view'], $getViews)
                            ? $options['view'] 
                            : $text['view'];

        /*
        |--------------------------------------------------------------------------
        | Adding Pagination Configuration into Constant
        |--------------------------------------------------------------------------
        */
        if ( ! defined('PAGINATION_CONFIG') ) {
            define('PAGINATION_CONFIG', $default);
        }
    }

    /**
     * Get the value of a configuration option.
     *
     * @param string $key 
     * The configuration key in dot notation (e.g., 'database.connections.mysql')
     * 
     * @param mixed $default 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return mixed
     * The value of the configuration option, or null if it doesn't exist
     */
    public static function config(string $key, $default = null)
    {
        // Convert the key to an array
        $parts = explode('.', $key);

        // Get the file name
        $filePath = base_path("config/{$parts[0]}.php");

        // Check if the configuration file exists
        if (file_exists($filePath)) {
            // Load the configuration array from the file
            $config = require($filePath);
        }

        // Remove the file name from the parts array
        unset($parts[0]);

        // Compile the configuration value
        foreach ($parts as $part) {
            if (isset($config[$part])) {
                $config = $config[$part];
            } else {
                $config = null;
            }
        }

        // try merging data if an array
        if(is_array($config) && is_array($default)){
            return array_merge($config, $default);
        }

        return $config ?? $default;
    }

}