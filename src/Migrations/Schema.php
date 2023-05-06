<?php

declare(strict_types=1);

namespace builder\Database\Migrations;


use PDOException;
use builder\Database\DB;
use builder\Database\Constants;
use builder\Database\Migrations\Blueprint;

class Schema extends Constants{
    
    static private $db;
    static protected $tableName;
    static protected $object;

    /**
     * Creating Instance of Database
     * 
     * @return void
     */
    static private function initSchemaDatabase() 
    {
        self::$db = new DB();
    }

    /**
     * Create a default string length for Database Schema
     * @param $length int $length The default length to use for string columns (default: 255)
     * 
     * @return void
     */
    static public function defaultStringLength($length = 255) 
    {
        // Check if the provided length is greater than the maximum allowed by MySQL.
        if ($length > 15950) {
            $length = 15950;
        }

        if( ! defined('ORM_MAX_STRING_LENGTH') ){
            define('ORM_MAX_STRING_LENGTH', $length);
        }
    }

    /**
     * Creating Indexs
     * @param string $tableName 
     * @param callable $callback
     * 
     * @return void
     */
    static public function create(?string $tableName, callable $callback) 
    {
        self::$object = new self;

        $callback(new Blueprint($tableName));
    }

    /**
     * Drop table
     * @param string $tableName 
     * 
     * @return array\dropTable
     */
    static public function dropTable(?string $tableName)
    {
        self::initSchemaDatabase();

        // Handle query
        try{
            // DROP TABLE IF EXISTS
            self::$db->query( "DROP TABLE {$tableName};" )->execute();

            echo "Table `{$tableName}` dropped successfully <br> \n";
        } catch (PDOException $e){
            echo preg_replace(
                '/^[ \t]+|[ \t]+$/m', '', 
                sprintf("<<\\Error code>> %s
                    <br><br>
                    <<\\PDO::ERROR>> %s <br> \n
                ", self::ERROR_404, $e->getMessage())
            );
            exit();
        }
    }

    /**
     * Drop column
     * @param string $tableName 
     * @param string $columnName 
     * 
     * @return object\dropColumn
     */
    static public function dropColumn(?string $tableName, ?string $columnName)
    {
        self::initSchemaDatabase();

        // if null
        if(is_null($columnName)){
            throw new \Exception('Table column name cannot be empty. Please pass a value.');
            return;
        }

        // Handle query
        try{
            // DROP COLUMN IF EXISTS
            self::$db->query( "ALTER TABLE {$tableName} DROP COLUMN {$columnName};" )->execute();

            // DROP COLUMN TRIGGERS 
            self::$db->query( "DROP TRIGGER IF EXISTS {$columnName}_created_at;" )->execute();

            // DROP COLUMN TRIGGERS 
            self::$db->query( "DROP TRIGGER IF EXISTS {$columnName}_updated_at;" )->execute();

            echo "Column `{$columnName}` on `{$tableName}` dropped successfully <br> \n";
        } catch (PDOException $e){
            echo preg_replace(
                '/^[ \t]+|[ \t]+$/m', '', 
                sprintf("<<\\Error code>> %s
                    <br><br>
                    <<\\PDO::ERROR>> %s <br> \n
                ", self::ERROR_404, $e->getMessage())
            );
            exit();
        }
    }
    
}