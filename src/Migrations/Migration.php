<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations;

use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Collections\Collection;
use Tamedevelopers\Database\Migrations\Traits\ManagerTrait;
use Tamedevelopers\Database\Migrations\Traits\FilePathTrait;
use Tamedevelopers\Database\Migrations\Traits\MigrationTrait;
use Tamedevelopers\Database\Migrations\Schema;

class Migration{

    use FilePathTrait,
        ManagerTrait,
        MigrationTrait;

    /**
     * constructor.
     */
    public function __construct()
    {
        self::$error = Constant::STATUS_400;
    }

    /**
     * Create migration name
     * @param string $table
     * @param string|null $type         
     * - [optional] <jobs, sessions> create schema with dummy Data
     * 
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public static function create($table, $type = null)
    {
        self::normalizeFolderStructure();

        self::initBaseDirectory();

        return self::runMigrationCreateTable($table, $type);
    }

    /**
     * Staring our migration
     * 
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public static function run()
    {
        // read file inside folders
        self::initBaseDirectory();

        // scan migration folder to get all files
        $files = self::scanDirectoryFiles(self::$migrations);

        $errorMessage = [];
        $errorstatus = Constant::STATUS_200;

        // run migration methods of included file
        foreach($files as $file){
            $migration = include_once "{$file}";

            $handle = $migration->up();
            if ($handle instanceof Collection) {
                $handle = $handle->toArray();
            }
            
            // If migration didn't return a result, fallback to last Schema result
            if (!is_array($handle)) {
                $handle = Schema::getLastResult();
            }

            // store all messages
            if (is_array($handle) && isset($handle['message'])) {
                $errorMessage[] = $handle['message'];
            }
            
            // error occured stop code execution
            if(is_array($handle) && isset($handle['status']) && $handle['status'] != Constant::STATUS_200){
                $errorstatus = $handle['status'];
                break;
            }
        }

        return new Collection([
            'status'    => $errorstatus, 
            'message'   => implode("\n", $errorMessage)
        ]);
    }
    
    /**
     * Run the migrations.
     * @return null
     */
    public function up()
    {
        return null;
    }
    
    /**
     * Drop database table
     * 
     * @param bool $force 
     * [optional] Default is false
     * Force drop all tables or throw an error on Foreign keys
     * 
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public function drop($force = false)
    {
        // read file inside folders
        self::initBaseDirectory();

        // scan migration folder to get all files in reverse dependency order
        // children first, then parents, to avoid foreign key constraint errors
        $files = self::scanDirectoryFilesDesc(self::$migrations);
        
        // run migration methods of included file
        $errorMessage   = [];
        $errorstatus    = Constant::STATUS_200;
        foreach($files as $file){
            $migration = include_once "{$file}";

            // call drop on the migration class; it may not return anything (void)
            $handle = $migration->drop($force);

            // normalize possible return types
            if ($handle instanceof Collection) {
                $handle = $handle->toArray();
            }

            // If migration didn't return a result, fallback to last Schema result
            if (!is_array($handle)) {
                $handle = Schema::getLastResult();
            }

            // store all messages, if available
            if (is_array($handle) && isset($handle['message'])) {
                $errorMessage[] = $handle['message'];
            }
            
            // error occured stop code execution
            if (is_array($handle) && isset($handle['status']) && $handle['status'] != Constant::STATUS_200) {
                $errorstatus = $handle['status'];
                break;
            }
        }

        return new Collection([
            'status'    => $errorstatus, 
            'message'   => implode("\n", $errorMessage)
        ]);
    }

}
