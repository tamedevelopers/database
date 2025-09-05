<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Support\Capsule\File;


trait AutoLoaderTrait{

    /**
     * Create dummy files if not exist
     * 
     * @return void
     */
    protected static function createDummy($path = null)
    {
        $paths = self::getPathsData($path);

        // only create when files are not present
        if(self::isDummyNotPresent($paths)){

            // create for database 
            self::createDatabase($paths);

            // create for init.php
            self::createInitPHP($paths);
    
            // create for gitignore
            self::createGitignore($paths);
    
            // create for htaccess
            self::createHtaccess($paths);
    
            // create for userini
            self::createIni($paths);
        }
    }

    /**
     * Create database.php file if not exist
     */
    private static function createDatabase($paths) : void
    {
        if(!File::exists($paths['database']['path'])){
            // create [dir] if not exists
            self::createConfigDirectory($paths);

            // Read the contents of the dummy file
            $dummyContent = File::get($paths['database']['dummy']);

            // Write the contents to the new file
            File::put($paths['database']['path'], $dummyContent);
        }
    }

    /**
     * Create init.php file if not exist
     */
    private static function createInitPHP($paths) : void
    {
        if(!File::exists($paths['init']['path'])){
            // Read the contents of the dummy file
            $dummyContent = File::get($paths['init']['dummy']);

            // Write the contents to the new file
            File::put($paths['init']['path'], $dummyContent);
        }
    }

    /**
     * Create .gitignore file if not exist
     */
    private static function createGitignore($paths) : void
    {
        if(!File::exists($paths['gitignore']['path'])){
            // Read the contents of the dummy file
            $dummyContent = File::get($paths['gitignore']['dummy']);

            // Write the contents to the new file
            File::put($paths['gitignore']['path'], $dummyContent);
        }
    }

    /**
     * Create .htaccess file if not exist
     */
    private static function createHtaccess($paths) : void
    {
        if(!File::exists($paths['htaccess']['path'])){
            // Read the contents of the dummy file
            $dummyContent = File::get($paths['htaccess']['dummy']);

            // Write the contents to the new file
            File::put($paths['htaccess']['path'], $dummyContent);
        }
    }

    /**
     * Create .userini file if not exist
     */
    private static function createIni($paths) : void
    {
        if(!File::exists($paths['userini']['path'])){
            // Read the contents of the dummy file
            $dummyContent = File::get($paths['userini']['dummy']);

            // Write the contents to the new file
            File::put($paths['userini']['path'], $dummyContent);
        }
    }

    /**
     * Create Configuration directory is not exists
     * 
     * @return void
     */
    private static function createConfigDirectory($paths = null)
    {
        // folder path
        $configFolder = str_replace(['database.dum', 'database.php'], '', $paths['database']['path']);

        // if config folder not found
        if(!File::isDirectory($configFolder)){
            File::makeDirectory($configFolder, 0777);
        }
    }

    /**
     * Check if dummy data is present
     * 
     * @return bool
     */
    protected static function isDummyNotPresent($paths)
    {
        $present = [false];

        // create for database 
        if(!file_exists($paths['database']['path'])){
            $present[] = true;
        }

        // create for tame bash script 
        if(!file_exists($paths['tame']['path'])){
            $present[] = true;
        }

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
    protected static function getPathsData($realPath = null)
    {
        $env        = new Env();
        $server     = Env::getServers('server');
        $serverPath = $env->cleanServerPath( $server );
        $realPath   = rtrim($env->cleanServerPath( $realPath ), '/');

        return [
            'database' => [
                'path'  => "{$serverPath}config/database.php",
                'dummy' => "{$realPath}/Dummy/dummyDatabase.dum",
            ],
            'init' => [
                'path'  => "{$serverPath}init.php",
                'dummy' => "{$realPath}/Dummy/dummyInit.dum",
            ],
            'gitignore' => [
                'path'  => "{$serverPath}.gitignore",
                'dummy' => "{$realPath}/Dummy/dummyGitIgnore.dum",
            ],
            'htaccess' => [
                'path'  => "{$serverPath}.htaccess",
                'dummy' => "{$realPath}/Dummy/dummyHtaccess.dum",
            ],
            'userini' => [
                'path'  => "{$serverPath}.user.ini",
                'dummy' => "{$realPath}/Dummy/dummyUserIni.dum",
            ]
        ];
    }

}