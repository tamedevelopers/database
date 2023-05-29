<?php

declare(strict_types=1);

namespace builder\Database;

use builder\Database\DB;
use builder\Database\Schema\EnvOrm;
use builder\Database\Capsule\Manager;

class EnvAutoLoad{

    static protected $default;

    /**
     * Star env configuration
     * 
     * @param array $options 
     * path \Path to .env file
     * 
     * @return void
     */
    static public function start(?array $options = [])
    {
        /*
        |--------------------------------------------------------------------------
        | Create default path and bg for errors
        |--------------------------------------------------------------------------
        */
        $default = array_merge([
            'path'  => null,
        ], $options);
        
        /*
        |--------------------------------------------------------------------------
        | Instance of class
        |--------------------------------------------------------------------------
        */
        $EnvOrm = new EnvOrm($default['path']);
        
        /*
        |--------------------------------------------------------------------------
        | Create a sample .env file if not exist in project
        |--------------------------------------------------------------------------
        */
        $EnvOrm::createOrIgnore();
        
        /*
        |--------------------------------------------------------------------------
        | Load environment file (associated to database)
        |--------------------------------------------------------------------------
        | This will automatically6 setup our database configuration if found 
        |
        */
        $loader = $EnvOrm::loadOrFail();
        
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
            $EnvOrm->dump( $loader['message'] );
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
                'env'       => $EnvOrm,
            ], $EnvOrm->getServers()));
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
     * - [optional]
     * 
     * @return void
     */
    static public function configPagination(?array $options = [])
    {
        /*
        |--------------------------------------------------------------------------
        | Create default path and bg for errors
        |--------------------------------------------------------------------------
        */
        $text       = Manager::$pagination_text;
        $getViews   = Manager::$pagination_views;
        
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
     * Create dummy files if not exist
     * 
     * @return void
     */
    static private function createDummy()
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
        $serverPath = str_replace('\\', '/', DOT_ENV_CONNECTION['server']);
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