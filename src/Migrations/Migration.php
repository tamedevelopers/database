<?php

declare(strict_types=1);

namespace builder\Database\Migrations;

use builder\Database\Constant;
use builder\Database\Migrations\Traits\ManagerTrait;
use builder\Database\Migrations\Traits\FilePathTrait;
use builder\Database\Migrations\Traits\MigrationTrait;

class Migration{

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
     * Create migration name
     * @param string $table_name 
     * @param string $type
     * - optional $jobs\To create dummy Jobs table Data
     * 
     * @return void
     */
    public static function create(?string $table_name, ?string $type = null)
    {
        self::initStatic();

        self::initBaseDirectory();

        self::runMigration($table_name, $type);
    }

    /**
     * Staring our migration
     * @param string $type 
     * @param string $column 
     * 
     * @return array
     */
    public static function run()
    {
        // read file inside folders
        $files = self::initBaseDirectory();

        // run migration methods of included file
        $errorMessage   = [];
        $errorstatus    = Constant::STATUS_200;
        foreach($files as $file){
            $migration = include_once "{$file}";

            // error
            $migration->up();

            // handle migration query data
            $handle = json_decode($_SESSION[self::getSession()] ?? "", true);

            // store all messages
            $errorMessage[] = $handle['message'];
            
            // error occured stop code execution
            if($handle['status'] != Constant::STATUS_200){
                $errorstatus = Constant::STATUS_404;
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
     * Run the migrations.
     *
     * @return mixed
     */
    public function up(){}
    
    /**
     * Drop database table
     * 
     * @param bool $force 
     * [optional] Default is false
     * Force drop all tables or throw an error on Foreign keys
     * 
     * @return mixed
     */
    public function drop($force = false)
    {
        // read file inside folders
        $files = self::initBaseDirectory();
        
        // run migration methods of included file
        $errorMessage   = [];
        $errorstatus    = Constant::STATUS_200;
        foreach($files as $file){
            $migration = include_once "{$file}";

            // error
            $handle = $migration->drop($force);

            // store all messages
            $errorMessage[] = $handle['message'];
            
            // error occured stop code execution
            if($handle['status'] != Constant::STATUS_200){
                $errorstatus = Constant::STATUS_404;
                break;
            }
        }

        return [
            'status'    => $errorstatus, 
            'message'   => implode("\n", $errorMessage)
        ];
    }

}
