<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations;

use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Collections\Collection;
use Tamedevelopers\Database\Migrations\Traits\ManagerTrait;
use Tamedevelopers\Database\Migrations\Traits\FilePathTrait;
use Tamedevelopers\Database\Migrations\Traits\MigrationTrait;

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
        
        // return $instance->session;
    }

    /**
     * Create migration name
     * @param string $table_name 
     * @param string|null $type
     * - optional $jobs\To create dummy Jobs table Data
     * 
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public static function create($table_name, $type = null)
    {
        self::normalizeFolderStructure();

        self::initBaseDirectory();

        return self::runMigrationCreateTable($table_name, $type);
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
        // unset($_SESSION[self::getSession()]);

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
    public function up(){

    }
    
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

    /**
     * Create API Response
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    protected static function makeResponse()
    {
        /*
        | ----------------------------------------------------------------------------
        | Database importation use. Below are the status code
        | ----------------------------------------------------------------------------
        |   if ->status === 404 (Failed to read file or File does'nt exists
        |   if ->status === 400 (Query to database error
        |   if ->status === 200 (Success importing to database
        */ 
        return new Collection([
            'status'    => self::$error,
            'path'      => self::$storagePath, 
            'message'   => self::$message
        ]);
    }

}
