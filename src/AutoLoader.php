<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\Traits\AutoLoaderTrait;
use Tamedevelopers\Database\Schema\Pagination\PaginatorAsset;

class AutoLoader{

    use AutoLoaderTrait;

    protected static $default;
    
    /**
     * Star env configuration
     * 
     * @param string|null $custom_path 
     * path \Path to .env file
     * - [optional] path \By default we use project root path
     * 
     * @return void
     */
    public static function start($custom_path = null)
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
        | or exit with error status code 
        |
        */
        $Env::loadOrFail();
        
        /*
        |--------------------------------------------------------------------------
        | Storing data into a Constant once everything is successful
        |--------------------------------------------------------------------------
        | We can now use on anywhere on our application 
        | Mostly to get our defined .env root Path
        */
        if ( ! defined('TAME_SERVER_CONNECT') ) {
            define('TAME_SERVER_CONNECT', $Env->getServers());
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

}