<?php

declare(strict_types=1);

namespace builder\Database\Traits;


trait AutoLoaderTrait{

    /**
     * Create dummy files if not exist
     * 
     * @return void
     */
    private static function createDummy($path = null)
    {
        $paths = self::getPathsData($path);

        // only create when files are not present
        if(self::isDummyNotPresent($paths)){

            // create for database 
            if(!file_exists($paths['database']['path'])){
                // create [dir] if not exists
                self::createConfigDirectory($paths);

                // Read the contents of the dummy file
                $dummyContent = file_get_contents($paths['database']['dummy']);
    
                // Write the contents to the new file
                file_put_contents($paths['database']['path'], $dummyContent);
            }

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
     * Create Configuration directory is not exists
     * 
     * @return void
     */
    private static function createConfigDirectory($paths = null)
    {
        // folder path
        $configFolder = str_replace(['database.dum', 'database.php'], '', $paths['database']['path']);

        // if config folder not found
        if(!is_dir($configFolder)){
            @mkdir($configFolder, 0777);
        }
    }


    /**
     * Check if dummy data is present
     * 
     * @return bool
     */
    private static function isDummyNotPresent($paths)
    {
        $present = [false];

        // create for database 
        if(!file_exists($paths['database']['path'])){
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
    private static function getPathsData($realPath)
    {
        $env        = DOT_ENV_CONNECTION['env'];
        $serverPath = $env->clean_path( DOT_ENV_CONNECTION['server'] );
        $realPath   = $env->clean_path( $realPath );

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
            ],
        ];
    }

}