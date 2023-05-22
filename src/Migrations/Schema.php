<?php

declare(strict_types=1);

namespace builder\Database\Migrations;


use PDOException;
use builder\Database\DB;
use builder\Database\Constants;
use builder\Database\Migrations\Blueprint;
use builder\Database\Migrations\Traits\ManagerTrait;

class Schema extends Constants{
    
    use ManagerTrait;
    
    static private $db;
    static protected $tableName;
    static protected $object;

    /**
     * Returns the style info
     * 
     * @return string
     */
    public static function getStyle()
    {
        $instance = new self();
        
        return $instance->style;
    }

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
     * @return mixed
     */
    static public function create(?string $tableName, callable $callback) 
    {
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

        // handle error
        $handle = self::checkDBConnect();
        if(is_array($handle)){
            return $handle;
        } 

        // Handle query
        try{
            // DROP TABLE IF EXISTS
            self::$db->query( "DROP TABLE {$tableName};" )->execute();

            return [
                'status'    => self::ERROR_200,
                'message'   => "Table `{$tableName}` dropped successfully <br> \n",
            ];
        } catch (PDOException $e){
            return [
                'status'    => self::ERROR_404,
                'message'   => preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    sprintf("<<\\Error code>> %s
                        <br><br>
                        <<\\PDO::ERROR>> %s <br> \n
                    ", self::ERROR_404, $e->getMessage())
                ),
            ];
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

        // handle error
        $handle = self::checkDBConnect();
        if(is_array($handle)){
            return $handle;
        } 

        // if empty
        if(empty($columnName)){
            return [
                'status'    => self::ERROR_404,
                'message'   => "Table column name cannot be empty. Please pass a value.<br>\n",
            ];
        }

        // Handle query
        try{
            // DROP COLUMN IF EXISTS
            self::$db->query( "ALTER TABLE {$tableName} DROP COLUMN {$columnName};" )->execute();

            // DROP COLUMN TRIGGERS 
            self::$db->query( "DROP TRIGGER IF EXISTS {$columnName}_created_at;" )->execute();

            // DROP COLUMN TRIGGERS 
            self::$db->query( "DROP TRIGGER IF EXISTS {$columnName}_updated_at;" )->execute();
            
            return [
                'status'    => self::ERROR_200,
                'message'   => "Column `{$columnName}` on `{$tableName}` dropped successfully <br> \n",
            ];
        } catch (PDOException $e){
            return [
                'status'    => self::ERROR_404,
                'message'   => preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    sprintf("<<\\Error code>> %s
                        <br><br>
                        <<\\PDO::ERROR>> %s <br> \n
                    ", self::ERROR_404, $e->getMessage())
                ),
            ];
        }
    }

    /**
     * Check database connection error
     * 
     * @return mixed
     */
    static private function checkDBConnect()
    {
        $style = self::getStyle();

        // if database connection is okay
        $dbConnection = self::$db->getConnection();
        if($dbConnection['status'] !== self::ERROR_200){
            return [
                'status'    => self::ERROR_404,
                'message'   => "Connection Error 
                                    <span style='background: #ee0707; {$style}'>
                                        Database Connection Error
                                    </span>
                                    `{$dbConnection['message']}` <br>\n",
            ];
        }
    }
    
}