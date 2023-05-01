<?php

declare(strict_types=1);

namespace builder\Database;

use builder\Database\DB;
use builder\Database\Capsule\Manager;
use builder\Database\Schema\OrmDotEnv;

class AutoloadEnv{

    static protected $default;

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
            'path'  => $options['path'] ?? null,
            'bg'    => $options['bg']   ?? 'default',
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
        | DOT_ENV_CONNECTION['path'] -> return array of data containing .env path
        */
        if ( ! defined('DOT_ENV_CONNECTION') ) {
            define('DOT_ENV_CONNECTION', [
                'self'      => $ormDotEnv,
                'self_path' => $loader,
            ]);
        }
        
        
        /*
        |--------------------------------------------------------------------------
        | Adding Database instance into DB Constant
        |--------------------------------------------------------------------------
        */
        if ( ! defined('DATABASE_CONNECTION') ) {
            define('DATABASE_CONNECTION', new DB());
        }
        
        
        /*
        |--------------------------------------------------------------------------
        | Automatically create dummy files
        |--------------------------------------------------------------------------
        */
        self::createDummy();
    }

    /**
     * Configura pagination data
     * 
     * @param array $options 
     * @return void
     */
    static public function configurePagination(?array $options = [])
    {
        /*
        |--------------------------------------------------------------------------
        | Create default path and bg for errors
        |--------------------------------------------------------------------------
        */
        $text       = Manager::$pagination_text;
        $default    = [
            'allow'     => $options['allow']            ?? 'disallow',
            'class'     => $options['class']            ?? null,
            'view'      => in_array($options['view']    ?? null, ['bootstrap', 'simple']) ? $options['view'] : $text['view'],
            'first'     => $options['first']            ?? $text['first'],
            'last'      => $options['last']             ?? $text['last'],
            'next'      => $options['next']             ?? $text['next'],
            'prev'      => $options['prev']             ?? $text['prev'],
            'span'      => $options['span']             ?? $text['span'],
            'showing'   => $options['showing']          ?? $text['showing'],
            'to'        => $options['to']               ?? $text['to'],
            'of'        => $options['of']               ?? $text['of'],
            'results'   => $options['results']          ?? $text['results'],
        ];

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
     * Create dummy files if not exist
     * 
     * @return void
     */
    static protected function createDummy()
    {
        $realPath = DOT_ENV_CONNECTION['self_path']['path'];
        $paths = [
            'init' => [
                'path'  => "{$realPath}\init.php",
                'dummy' => realpath(__DIR__) . "\Dummy\dummyInit.php",
            ],
            'gitignore' => [
                'path'  => "{$realPath}\.gitignore",
                'dummy' => realpath(__DIR__) . "\Dummy\dummyGitIgnore.php",
            ],
            'htaccess' => [
                'path'  => "{$realPath}\.htaccess",
                'dummy' => realpath(__DIR__) . "\Dummy\dummyHtaccess.php",
            ],
            'phpini' => [
                'path'  => "{$realPath}\php.ini",
                'dummy' => realpath(__DIR__) . "\Dummy\dummyPhpIni.php",
            ],
            'userini' => [
                'path'  => "{$realPath}\.user.ini",
                'dummy' => realpath(__DIR__) . "\Dummy\dummyUserIni.php",
            ],
        ];

        // create for init 
        if(!file_exists($paths['init']['path']) && !is_dir($paths['init']['path'])){
            @$fsource = fopen($paths['init']['path'], 'w+s');
            if(is_resource($fsource)){
                @fwrite($fsource, file_get_contents($paths['init']['dummy']));
                @fclose($fsource);
            }
        }

        // create for gitignore
        if(!file_exists($paths['gitignore']['path']) && !is_dir($paths['gitignore']['path'])){
            @$fsource = fopen($paths['gitignore']['path'], 'w+s');
            if(is_resource($fsource)){
                @fwrite($fsource, file_get_contents($paths['gitignore']['dummy']));
                @fclose($fsource);
            }
        }

        // create for htaccess
        if(!file_exists($paths['htaccess']['path']) && !is_dir($paths['htaccess']['path'])){
            @$fsource = fopen($paths['htaccess']['path'], 'w+s');
            if(is_resource($fsource)){
                @fwrite($fsource, file_get_contents($paths['htaccess']['dummy']));
                @fclose($fsource);
            }
        }

        // create for phpini
        if(!file_exists($paths['phpini']['path']) && !is_dir($paths['phpini']['path'])){
            @$fsource = fopen($paths['phpini']['path'], 'w+s');
            if(is_resource($fsource)){
                @fwrite($fsource, file_get_contents($paths['phpini']['dummy']));
                @fclose($fsource);
            }
        }

        // create for userini
        if(!file_exists($paths['userini']['path']) && !is_dir($paths['userini']['path'])){
            @$fsource = fopen($paths['userini']['path'], 'w+s');
            if(is_resource($fsource)){
                @fwrite($fsource, file_get_contents($paths['userini']['dummy']));
                @fclose($fsource);
            }
        }

    }
    
}