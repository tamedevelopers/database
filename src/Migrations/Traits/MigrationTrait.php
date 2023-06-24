<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;

use builder\Database\Env;

/**
 * 
 * @property mixed $style
 */
trait MigrationTrait{

    private static $database;
    private static $migrations;
    private static $seeders;
    
    /**
     * Run Migrations
     * 
     * @return mixed
     */
    private static function runMigration(?string $table_name, ?string $type = null) 
    {
        // table name
        $case_table = self::toSnakeCase($table_name);

        // Date convert
        $fileName = sprintf( "%s_%s_%s", date('Y_m_d'), substr((string) time(), 4), "{$case_table}.php" );

        // real path
        $realPath   = str_replace('\\', '/', rtrim(realpath(__DIR__), "/\\"));

        // get directory
        $dummyPath = "{$realPath}/../../Dummy/dummyMigration.dum";


        // If type creation passed
        if(!empty($type) && in_array(strtolower($type), ['job', 'jobs'])){
            // create a jobs table
            $dummyPath = "{$realPath}/../../Dummy/dummyJobsMigration.dum";
        } elseif(!empty($type) && in_array(strtolower($type), ['session', 'sessions'])){
            // create a sessions table
            $dummyPath = "{$realPath}/../../Dummy/dummySessionsMigration.dum";
        }

        // dummy content
        $dummyContent = str_replace('dummy_table', $case_table, file_get_contents($dummyPath));

        // absolute path
        $absoluteFile = self::$migrations . $fileName;

        // check if file exists already
        $style = self::$style;
        if(file_exists($absoluteFile) && !is_dir($absoluteFile)){
            echo sprintf("Table `%s` 
                        <span style='background: #ee0707; {$style}'> 
                            Failed
                        </span> 
                        Schema already exists <br> \n", basename($fileName, '.php'));
            return;
        }

        // start writting
        // Write the contents to the new files
        file_put_contents($absoluteFile, $dummyContent);

        // Flush the output buffer
        ob_flush();
        flush();

        sleep(1);

        echo sprintf("Table `%s` has been created
                    <span style='background: #027b02; {$style}'> 
                        Successfully
                    </span> <br> \n", basename($fileName, '.php'));

        // Flush the output buffer again
        ob_flush();
        flush();
    }
    
    /**
     * Creating Managers
     * 
     * @return mixed
     */
    private static function initBaseDirectory() 
    {
        self::initStatic();

        // check if database folder not exist
        if(!is_dir(self::$database)){
            @mkdir(self::$database, 0777);

            // gitignore fle path
            $gitignore = sprintf("%s.gitignore", self::$database);

            // create file if not exist
            if (!file_exists($gitignore) && !is_dir($gitignore)) {
                // Write the contents to the new file
                file_put_contents($gitignore, preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    ".
                    /database
                    .env"
                ));
            }
        }

        // if migrations folder not found
        if(!is_dir(self::$migrations)){
            @mkdir(self::$migrations, 0777);
        }

        // if seeders folder not found
        if(!is_dir(self::$seeders)){
            @mkdir(self::$seeders, 0777);
        }

        if(!is_dir(self::$migrations)){
            throw new \Exception( 
                sprintf("Path to dabatase[dir] not found ---> `%s`", self::$migrations) 
            );
        } 

        // read file inside folders
        return self::directoryfiles(self::$migrations);
    }

    /**
     * Creating Managers
     * @param string $tableName 
     * 
     * @return void
     */
    private static function initStatic() 
    {
        // if not defined
        if ( ! defined('DOT_ENV_CONNECTION') ) {
            self::$database = (new Env)->getDirectory();
        } else{
            // once we run env autoloader
            // we have access to global Constant DOT_ENV_CONNECTION
            self::$database = DOT_ENV_CONNECTION['server'];
        }

        self::$database     .= "database/";
        self::$migrations   = self::$database . "migrations/";
        self::$seeders      = self::$database . "seeders/";
    }
    
    /**
     * drop database column
     * @param string $input
     *
     * @return string 
     * - String toSnakeCase
     */
    private static function toSnakeCase(?string $input)
    {
        $output = preg_replace_callback(
            '/[A-Z]/',
            function ($match) {
                return '_' . strtolower($match[0]);
            },
            $input
        );

        return ltrim($output, '_');
    }

    /**
     * Getting all files in directory
     * @param string $directory 
     * 
     * @return array|string
     */
    private static function directoryfiles(?string $directory)
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