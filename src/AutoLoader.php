<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\Traits\AutoLoaderTrait;
use Tamedevelopers\Database\Schema\Pagination\PaginatorAsset;

class AutoLoader
{
    use AutoLoaderTrait;

    protected static $default;

    /**
     * Boot the AutoLoader::start.
     * If the constant 'TAME_AUTOLOADER_BOOT' is not defined, 
     * it defines it and starts the debugger automatically 
     * 
     * So that this is only called once in entire application life-cycle
     * 
     * @param string|null $path 
     * @param bool $createDummy 
     * @return void
     */
    public static function boot($path = null, $createDummy = true)
    {
        if(!defined('TAME_AUTOLOADER_BOOT')){
            // start auto loader
            self::start($path, $createDummy);

            // Define boot logger as true
            define('TAME_AUTOLOADER_BOOT', 1);
        } 
    }
    
    /**
     * Star env configuration
     * 
     * @param string|null $path 
     * path \Path to .env file
     * - [optional] path \By default we use project root path
     * 
     * @param bool $createDummy 
     * 
     * @return void
     */
    public static function start($path = null, $createDummy = true)
    {
        /*
        |--------------------------------------------------------------------------
        | Instance of class
        |--------------------------------------------------------------------------
        */
        $env = new Env($path);
        
        /*
        |--------------------------------------------------------------------------
        | Create a sample .env file if not exist in project
        |--------------------------------------------------------------------------
        */
        $env::createOrIgnore();
        
        /*
        |--------------------------------------------------------------------------
        | Load environment file (associated to database)
        |--------------------------------------------------------------------------
        | This will automatically6 setup our database configuration if found 
        | or exit with error status code 
        |
        */
        $env::loadOrFail();
        
        /*
        |--------------------------------------------------------------------------
        | Automatically create dummy files
        |--------------------------------------------------------------------------
        */
        if($createDummy){
            self::createDummy(realpath(__DIR__));
        }
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
        if ( ! defined('TAME_PAGI_CONFIG') ) {
            define('TAME_PAGI_CONFIG', $default);
        }
    }

}