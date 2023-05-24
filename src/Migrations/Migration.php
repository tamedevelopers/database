<?php

declare(strict_types=1);

namespace builder\Database\Migrations;

use builder\Database\Constants;
use builder\Database\Schema\EnvOrm;
use builder\Database\Migrations\Traits\ManagerTrait;
use builder\Database\Migrations\Traits\FilePathTrait;

class Migration extends Constants{

    use FilePathTrait,
        ManagerTrait;

    static private $database;
    static private $migrations;
    static private $seeders;
    

    /**
     * Returns Session String
     * 
     * @return string
     */
    public static function getSession()
    {
        $instance = new self();
        
        return $instance->session;
    }

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
            self::$database = (new EnvOrm)->getDirectory();
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
     * Staring our migration
     * @param string $type 
     * @param string $column 
     * 
     * @return array|string\initBaseDirectory
     */
    static public function run(?string $type = null, ?string $column = null)
    {
        // read file inside folders
        $files = self::initBaseDirectory();

        // use default
        if(empty($type)){
            $type = 'up';
        }

        // Check if method exist
        if(!in_array(strtolower($type), ['up', 'drop', 'column'])  || !method_exists(__CLASS__, strtolower($type))){
            return [
                'status'    => self::ERROR_404,
                'message'   => sprintf("The method or type `%s` you're trying to call doesn't exist", $type)
            ];
        }

        // run migration methods of included file
        $errorMessage   = [];
        $errorstatus    = self::ERROR_200;
        foreach($files as $file){
            $migration = include_once "{$file}";

            // error
            $migration->{$type}($column);
            
            // handle migration query data
            $handle = json_decode($_SESSION[self::getSession()] ?? [], true);

            // store all messages
            $errorMessage[] = $handle['message'];
            
            // error occured stop code execution
            if($handle['status'] != self::ERROR_200){
                $errorstatus = self::ERROR_404;
                break;
            }
        }

        // unset session
        unset($_SESSION[self::getSession()]);

        return [
            'status'    => $errorstatus, 
            'message'   => implode("\n", $errorMessage)
        ];
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
        $fileName = sprintf( "%s_%s_%s", date('Y_m_d'), strtotime('today'), "{$case_table}.php" );

        // real path
        $realPath   = str_replace('\\', '/', rtrim(realpath(__DIR__), "/\\"));

        // get directory
        $dummyPath = "{$realPath}/../Dummy/dummyMigration.php";


        // If type creation passed
        if(!empty($type) && in_array(strtolower($type), ['job', 'jobs'])){
            // create a jobs table
            $dummyPath = "{$realPath}/../Dummy/dummyJobsMigration.php";
        } elseif(!empty($type) && in_array(strtolower($type), ['session', 'sessions'])){
            // create a sessions table
            $dummyPath = "{$realPath}/../Dummy/dummySessionsMigration.php";
        }

        // dummy content
        $dummyContent = str_replace('dummy_table', $case_table, file_get_contents($dummyPath));

        // absolute path
        $absoluteFile = self::$migrations . $fileName;

        // check if file exists already
        $style = (new self)->style;
        if(file_exists($absoluteFile) && !is_dir($absoluteFile)){
            echo sprintf("Table `%s` 
                        <span style='background: #ee0707; {$style}'> 
                            Failed
                        </span> 
                        Schema already exists <br> \n", basename($fileName, '.php'));
            return;
        }

        // start writting
        // Write the contents to the new file
        file_put_contents($absoluteFile, $dummyContent);

        echo sprintf("Table `%s` has been created
                    <span style='background: #027b02; {$style}'> 
                        Successfully
                    </span> <br> \n", basename($fileName, '.php'));
    }
    
    /**
     * Run the migrations.
     *
     * @return mixed
     */
    public function up(){}
    
    /**
     * Drop database table
     *
     * @return mixed
     */
    public function drop(){}

    /**
     * drop database column
     * @param string $column
     *
     * @return mixed
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
            $value = rtrim($directory, '/') . "/{$value}";
        });
        
        return $readDir;
    }

}
