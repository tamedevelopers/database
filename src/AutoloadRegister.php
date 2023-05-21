<?php

declare(strict_types=1);

namespace builder\Database;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use builder\Database\Schema\OrmDotEnv;

class AutoloadRegister{
    
    /**
     * Autoload All Folder and Sub-Folder files
     * 
     * @param string $path_to_folder
     * - Specify the folder to autoload
     * - Do not include the root path, as The Application already have a copy of your path
     * - e.g [classes] or [app/main]
     * 
     * @return void
     */
    static public function load(?string $path_to_folder = null)
    {
        // If path is not null
        if(!empty($path_to_folder)){
            spl_autoload_register(function ($className) use($path_to_folder) {
                self::register($path_to_folder);
            });
        }
    }

    /**
     * Register method
     * 
     * @param string  $path_to_folder
     * 
     * @return void
     */
    static private function register($path_to_folder)
    {
        // directory full path
        $directory = self::convertPath($path_to_folder);

        // Create a recursive iterator to iterate through the directory
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory, 
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        // Loop through the iterator
        foreach ($iterator as $file) {
            // Check if the item is a file and has a PHP extension
            if ($file->isFile() && $file->getExtension() === 'php') {
                include_once $file->getPathname();
            }
        }
    }

    /**
     * Convert path
     * 
     * @param string  $path_to_folder
     * 
     * @return string
     */
    static private function convertPath($path_to_folder)
    {
        $ormDotEnv = new OrmDotEnv();
        return str_replace(
            '/', 
            '\\', 
            $ormDotEnv->getDirectory() . "/{$path_to_folder}" 
        );
    }
    
}