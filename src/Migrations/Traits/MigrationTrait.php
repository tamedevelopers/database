<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations\Traits;

use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Env;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Process\HttpRequest;

/**
 * 
 * @property mixed $style
 */
trait MigrationTrait{

    private static $database;
    private static $migrations;
    private static $seeders;
    private static $error;
    private static $message;
    private static $storagePath;


    /**
     * Normalize folser structure for migrations and seeders
     *
     * @return void
     */
    private static function normalizeFolderStructure()
    {
        // collection of migration and seeders path
        self::$database     = Env::getServers('server') . "database/";
        self::$migrations   = self::$database . "migrations/";
        self::$seeders      = self::$database . "seeders/";
    }

    /**
     * Get Dummy real path data
     *
     * @return array
     */
    private static function getDummyParts()
    {
        // real path
        $realPath = Str::replace('\\', '/', rtrim(realpath(__DIR__), "/\\"));

        return [
            'default'   => "{$realPath}/../../Dummy/dummyMigration.dum",
            'job'       => "{$realPath}/../../Dummy/dummyJobsMigration.dum",
            'session'   => "{$realPath}/../../Dummy/dummySessionsMigration.dum",
        ];
    }
    
    /**
     * Run Migrations
     *
     * @param  string $table_name
     * @param  string|null $type
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    private static function runMigrationCreateTable($table_name, $type = null) 
    {
        // table name
        $table  = Str::snake($table_name ?? '');
        $type   = Str::lower($type);
        $style  = self::$style;

        // Date convert
        $fileName = sprintf( "%s_%s", 
                    date('Y_m_d'), 
                    "create_{$table}_table.php" );

        // path
        $path = self::getDummyParts();

        // get directory
        $dummyPath = match ($type) {
            !empty($type) && in_array($type, ['job', 'jobs']) => $path['job'],
            !empty($type) && in_array($type, ['session', 'sessions']) => $path['session'],
            default => $path['default'],
        };

        // dummy content
        $dummyContent = Str::replace('{{TABLE}}', $table, File::get($dummyPath));

        // absolute path
        self::$storagePath = self::$migrations . $fileName;

        // browser break
        $isConsole = HttpRequest::runningInConsole();
        $message = [
            'console_error' => "Migration <b>[%s]</b> already exists.",
            'console_success' => "Migration <b>[%s]</b> created successfully.",
            'browser_error' => "<span style='background: #ee0707; {$style}'>already exists.</span><br>",
            'browser_success' => "<span style='background: #027b02; {$style}'>created successfully.</span><br>",
        ];
        
        if(File::exists(self::$storagePath)){
            self::$error = Constant::STATUS_400;
            self::$message = sprintf(
                $isConsole ? $message['console_error'] : $message['browser_error'],
                self::$storagePath
            );
            return self::makeResponse();
        }

        // start writting
        // Write the contents to the new files
        File::put(self::$storagePath, $dummyContent);

        self::$error = Constant::STATUS_200;
        self::$message = sprintf(
            $isConsole ? $message['console_success'] : $message['browser_success'], 
            self::$storagePath
        );

        return self::makeResponse();
    }
    
    /**
     * Creating Managers
     * 
     * @return mixed
     */
    private static function initBaseDirectory() 
    {
        self::normalizeFolderStructure();

        // check if database folder not exist
        if(!File::isDirectory(self::$database)){
            @File::makeDirectory(self::$database, 0777);

            // gitignore fle path
            $gitignore = sprintf("%s.gitignore", self::$database);

            // create file if not exist
            if (!File::exists($gitignore) && !is_dir($gitignore)) {
                // Write the contents to the new file
                File::put($gitignore, preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    ".
                    /database
                    .env"
                ));
            }
        }

        // if migrations folder not found
        if(!File::isDirectory(self::$migrations)){
            File::makeDirectory(self::$migrations, 0777);
        }

        // if seeders folder not found
        if(!File::isDirectory(self::$seeders)){
            File::makeDirectory(self::$seeders, 0777);
        }

        if(!File::isDirectory(self::$migrations)){
            throw new \Exception(
                sprintf("Path to dabatase[dir] not found ---> `%s`", self::$migrations) 
            );
        } 
    }

    /**
     * Getting all files in directory
     * @param string $directory 
     * 
     * @return array|string
     */
    private static function scanDirectoryFiles($directory)
    {
        // read file inside folders
        $readDir = scandir($directory);

        unset($readDir[0]);
        unset($readDir[1]);

        // change value to absolute path to file
        array_walk($readDir, function(&$value, $index) use($directory) {
            $value = rtrim($directory, '/') . "/{$value}";
        });
        
        return $readDir;
    }

}