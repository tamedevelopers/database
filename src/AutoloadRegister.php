<?php

declare(strict_types=1);

namespace builder\Database;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use builder\Database\Traits\ServerTrait;

class AutoloadRegister{
    
    use ServerTrait;
    
    /**
     * The base directory to scan for classes and files.
     * @var string
     */
    private static $baseDirectory;

    /**
     * The class map that stores the class names and their corresponding file paths.
     * @var array
     */
    private static $classMap = [];

    /**
     * The file map that stores the file paths and their corresponding relative paths.
     * @var array
     */
    private static $fileMap = [];

    /**
     * Autoload function to load class and files in a given folder
     *
     * @param string|array $baseDirectory 
     * - The directory path to load
     * - Do not include the root path, as The Application already have a copy of your path
     * - e.g [classes] or [app/main]
     * 
     * @return void
     */
    public static function load(string|array $baseDirectory)
    {
        if(is_array($baseDirectory)){
            foreach($baseDirectory as $directory){
                self::$baseDirectory = self::formatWithBaseDirectory($directory);
                // only allow is an existing directory
                if(is_dir(self::$baseDirectory)){
                    self::boot();
                }
            }
        } else{
            self::$baseDirectory = self::formatWithBaseDirectory($baseDirectory);
            // only allow is an existing directory
            if(is_dir(self::$baseDirectory)){
                self::boot();
            }
        }
    }

    /**
     * Boot the autoloader by setting the base directory, 
     * - Scanning the directory, and registering the autoload method.
     * @return void
     */
    private static function boot()
    {
        self::generateClassMap();
        self::generateFileMap();
        self::loadFiles();
        spl_autoload_register([__CLASS__, 'loadClass']);
    }

    /**
     * Autoload function to load the class file based on the class name.
     *
     * @param string $className The name of the class to load.
     * @return void
     */
    private static function loadClass($className)
    {
        $filePath = self::$classMap[$className] ?? null;
        if ($filePath && file_exists($filePath)) {
            require_once $filePath;
        }
    }

    /**
     * Load the files from the file map.
     *
     * @return void
     */
    private static function loadFiles()
    {
        foreach (self::$fileMap as $fileName => $filePath) {
            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }

    /**
     * Generate the class map by scanning the base directory and its subdirectories.
     *
     * @return void
     */
    private static function generateClassMap()
    {
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::$baseDirectory)
        );

        foreach ($fileIterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath   = $file->getPathname();
                $className  = self::getClassName($filePath);
                if (!is_null($className)) {
                    self::$classMap[ltrim($className, '\\')] = self::pathReplacer($filePath);
                }
            }
        }
    }

    /**
     * Generate the file map by scanning the base directory and its subdirectories.
     *
     * @return void
     */
    private static function generateFileMap()
    {
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::$baseDirectory)
        );

        foreach ($fileIterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $className = self::getClassName($filePath);

                if ($className === null) {
                    $relativePath = self::getRelativePath($filePath);
                    self::$fileMap[$relativePath] = self::pathReplacer($filePath);
                }
            }
        }
    }

    /**
     * Get the relative path from the file path.
     *
     * @param string $filePath The file path.
     * @return string The relative path.
     */
    private static function getRelativePath($filePath)
    {
        $relativePath = substr($filePath, strlen(self::$baseDirectory));
        return ltrim($relativePath, '/\\');
    }

    /**
     * Get the class name from the file path.
     *
     * @param string $filePath The file path.
     * @return string|null The class name, or null if not found.
     */
    private static function getClassName($filePath)
    {
        $namespace  = '';
        $content    = file_get_contents($filePath);
        $tokens     = token_get_all($content);
        $count      = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR) {
                        $namespace .= $tokens[$j][1];
                    } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_TRAIT) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === '{' || $tokens[$j] === 'extends' || $tokens[$j] === 'implements' || $tokens[$j] === 'use') {
                        break;
                    } elseif ($tokens[$j][0] === T_STRING) {
                        return $namespace . '\\' . $tokens[$j][1];
                    }
                }
            }
        }
        return;
    }
    
}