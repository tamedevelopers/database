<?php

declare(strict_types=1);

namespace builder\Database\Migrations;

use builder\Database\Schema\OrmDotEnv;
use builder\Database\Migrations\Traits\FilePathTrait;

class Migration{

    use FilePathTrait;

    static private $database;
    static private $migrations;
    static private $seeders;
    
    /**
     * Creating Managers
     * @param string $tableName 
     * 
     * @return void
     */
    static private function initStatic() 
    {
        // if not defined
        if ( ! defined('DOT_ENV_CONNECTION') ) {
            self::$database = (new OrmDotEnv)::$path;
        } else{
            // once we run env autoloader
            // we have access to global Constant DOT_ENV_CONNECTION
            self::$database = DOT_ENV_CONNECTION['self_path']['path'];
        }

        self::$database     .= "database/";
        self::$migrations   = self::$database . "migrations/";
        self::$seeders      = self::$database . "seeders/";
    }
    
    /**
     * Creating Managers
     * 
     * @return array|string\initBaseDirectory
     */
    static private function initBaseDirectory() 
    {
        self::initStatic();

        // check if database folder not exist
        if(!is_dir(self::$database)){
            @mkdir(self::$database, 0777);

            // gitignore fle path
            $gitignore = sprintf("%s.gitignore", self::$database);

            // create file if not exist
            if (!file_exists($gitignore) && !is_dir($gitignore)) {
                @$fsource = fopen($gitignore, 'w+');
                if(is_resource($fsource)){
                    fwrite($fsource, preg_replace(
                        '/^[ \t]+|[ \t]+$/m', '', 
                        ".
                        /database
                        .env"
                    ));
                    fclose($fsource);
                }
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
     * Staring our migration
     * @param string $type 
     * @param string $column 
     * 
     * @return array|string\initBaseDirectory
     */
    static public function run(?string $type = null, ?string $column = null)
    {
        // read file inside folders
        $readDir = self::initBaseDirectory();

        // use default
        if(empty($type)){
            $type = 'up';
        }

        // Check if method exist
        if(!in_array(strtolower($type), ['create', 'up', 'drop', 'column'])  || !method_exists(__CLASS__, strtolower($type))){
            throw new \Exception( 
                sprintf("The method or type `%s` you're trying to call doesn't exist", $type)
            );
        }

        // run migration methods of included file
        foreach($readDir as $dir){
            $migration = include_once "{$dir}";

            $migration->{$type}($column);
        }
    }
    
    /**
     * Create migration name
     * @param string $table_name 
     * @param string $type
     * - optional $jobs\To create dummy Jobs table Data
     * 
     * @return void
     */
    static public function create(?string $table_name, ?string $type = null)
    {
        self::initStatic();

        self::initBaseDirectory();

        // table name
        $case_table = self::toSnakeCase($table_name);

        // Date convert
        $fileName = sprintf( "%s_%s_%s", date('Y_m_d'), strtotime('now'), "{$case_table}.php" );

        // get directory
        $dummyPath = realpath(__DIR__) . "\..\Dummy\dummyMigration.php";


        // If type creation passed
        if(!empty($type) && in_array(strtolower($type), ['job', 'jobs'])){
            // create a jobs table
            $dummyPath = realpath(__DIR__) . "\..\Dummy\dummyJobsMigration.php";
        } elseif(!empty($type) && in_array(strtolower($type), ['session', 'sessions'])){
            // create a sessions table
            $dummyPath = realpath(__DIR__) . "\..\Dummy\dummySessionsMigration.php";
        }

        // dummy content
        $dummyContent = str_replace('dummy_table', $case_table, file_get_contents($dummyPath));

        // start writting
        @$fsource = fopen(self::$migrations . $fileName, 'w+s');
        if(is_resource($fsource)){
            @fwrite($fsource, $dummyContent );
            @fclose($fsource);
        }

        echo sprintf("Table `%s` has been created successfully <br> \n", basename($fileName, '.php'));
    }
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){}
    

    /**
     * Drop database table
     *
     * @return void
     */
    public function drop(){}
    

    /**
     * drop database column
     * @param string $column
     *
     * @return void
     */
    public function column(?string $column){}

    /**
     * drop database column
     * @param string $input
     *
     * @return string \toSnakeCase
     */
    static private function toSnakeCase(?string $input)
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
     * @return array|string\directoryfiles
     */
    static private function directoryfiles(?string $directory)
    {
        // read file inside folders
        $readDir = scandir($directory);

        unset($readDir[0]);
        unset($readDir[1]);

        // change value to absolute path to file
        array_walk($readDir, function(&$value, $index) use($directory) {
            $value = "{$directory}/{$value}";
        });
        
        return $readDir;
    }

}
