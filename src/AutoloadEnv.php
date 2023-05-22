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
        if($loader['status'] != 200){
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
        $paths = self::getPathsData();

        // only create when files are not present
        if(self::isDummyNotPresent()){
            // create for init 
            if(!file_exists($paths['init']['path'])){
                // Read the contents of the dummy file
                $dummyContent = file_get_contents($paths['init']['dummy']);
    
                // Write the contents to the new file
                file_put_contents($paths['init']['path'], $dummyContent);
            }
    
            // create for gitignore
            if(!file_exists($paths['gitignore']['path'])){
                // Read the contents of the dummy file
                $dummyContent = file_get_contents($paths['gitignore']['dummy']);
    
                // Write the contents to the new file
                file_put_contents($paths['gitignore']['path'], $dummyContent);
            }
    
            // create for htaccess
            if(!file_exists($paths['htaccess']['path'])){
                // Read the contents of the dummy file
                $dummyContent = file_get_contents($paths['htaccess']['dummy']);
    
                // Write the contents to the new file
                file_put_contents($paths['htaccess']['path'], $dummyContent);
            }
    
            // create for phpini
            if(!file_exists($paths['phpini']['path'])){
                // Read the contents of the dummy file
                $dummyContent = file_get_contents($paths['phpini']['dummy']);
    
                // Write the contents to the new file
                file_put_contents($paths['phpini']['path'], $dummyContent);
            }
    
            // create for userini
            if(!file_exists($paths['userini']['path'])){
                // Read the contents of the dummy file
                $dummyContent = file_get_contents($paths['userini']['dummy']);
    
                // Write the contents to the new file
                file_put_contents($paths['userini']['path'], $dummyContent);
            }
        }
    }

    /**
     * Check if dummy data is present
     * 
     * @return bool
     */
    static private function isDummyNotPresent()
    {
        $paths = self::getPathsData();
        $present = [false];

        // create for init 
        if(!file_exists($paths['init']['path'])){
            $present[] = true;
        }

        // create for gitignore
        if(!file_exists($paths['gitignore']['path'])){
            $present[] = true;
        }

        // create for htaccess
        if(!file_exists($paths['htaccess']['path'])){
            $present[] = true;
        }

        // create for phpini
        if(!file_exists($paths['phpini']['path'])){
            $present[] = true;
        }

        // create for userini
        if(!file_exists($paths['userini']['path'])){
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

    /**
     * Get all dummy contents path data
     * 
     * @return array
     */
    static private function getPathsData()
    {
        $serverPath = str_replace('\\', '/', DOT_ENV_CONNECTION['self_path']['path']);
        $realPath   = str_replace('\\', '/', rtrim(realpath(__DIR__), "/\\"));
        return [
            'init' => [
                'path'  => "{$serverPath}init.php",
                'dummy' => "{$realPath}/Dummy/dummyInit.php",
            ],
            'gitignore' => [
                'path'  => "{$serverPath}.gitignore",
                'dummy' => "{$realPath}/Dummy/dummyGitIgnore.php",
            ],
            'htaccess' => [
                'path'  => "{$serverPath}.htaccess",
                'dummy' => "{$realPath}/Dummy/dummyHtaccess.php",
            ],
            'phpini' => [
                'path'  => "{$serverPath}php.ini",
                'dummy' => "{$realPath}/Dummy/dummyPhpIni.php",
            ],
            'userini' => [
                'path'  => "{$serverPath}.user.ini",
                'dummy' => "{$realPath}/Dummy/dummyUserIni.php",
            ],
        ];
    }
    
}