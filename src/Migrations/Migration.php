<?php

declare(strict_types=1);

namespace builder\Database\Migrations;

use builder\Database\Constants;
use builder\Database\Schema\EnvOrm;
use builder\Database\Migrations\Traits\ManagerTrait;
use builder\Database\Migrations\Traits\FilePathTrait;
use builder\Database\Migrations\Traits\MigrationTrait;

class Migration extends Constants{

    use FilePathTrait,
        ManagerTrait,
        MigrationTrait;
    
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
     * Staring our migration
     * @param string $type 
     * @param string $column 
     * 
     * @return array
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

        self::runMigration($table_name, $type);
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
    

}
