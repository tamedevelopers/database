<?php

declare(strict_types=1);

namespace builder\Database\Migrations;


use PDO;
use PDOException;
use builder\Database\DB;
use builder\Database\Constant;
use builder\Database\Migrations\Blueprint;
use builder\Database\Migrations\Traits\ManagerTrait;

class Schema{
    
    use ManagerTrait;
    
    /**
     * Instance of Database Object
     *
     * @var object\builder\Database\DB
     */
    private static $db;
    
    /**
     * Instance of Database Object
     *
     * @var mixed
     */
    private static $pdo;


    /**
     * Creating Instance of Database
     * 
     * @return void
     */
    private static function initSchemaDatabase() 
    {
        self::$db = DB::connection();
        self::$pdo = self::$db->getPDO();
    }

    /**
     * Create a default string length for Database Schema
     * @param $length int $length The default length to use for string columns (default: 255)
     * 
     * @return void
     */
    public static function defaultStringLength($length = 255) 
    {
        // MySQL 5.0.3 and later: 65,535 characters (bytes)
        // MySQL 5.0.3 to 5.0.22: 65,532 characters (bytes)
        // MySQL 5.0.0 to 5.0.3: 4,096 characters (bytes)
        // MySQL 3.23.0 to 4.1.x: 255 characters (bytes)
        // Check if the provided length is greater than the maximum allowed by MySQL.
        // We're going to set max legnth to `4096` Char as v:5.0.0
        if ($length > 4096) {
            $length = 4096;
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
     * @return \builder\Database\Migrations\Blueprint
     */
    public static function create(?string $tableName, callable $callback) 
    {
        $callback(new Blueprint($tableName));
    }

    /**
     * Update the default value of a column in a table.
     *
     * @param string $table
     * - The name of the table.
     * 
     * @param string $column
     * - The name of the column.
     * 
     * @param mixed  $value
     * - The new default value for the column.
     * 
     * @return array
     */
    public static function updateColumnDefaultValue(?string $table, ?string $column, mixed $value = null)
    {
        self::initSchemaDatabase();

        // handle error
        $handle = self::checkDBConnect();
        if(is_array($handle)){
            return $handle;
        } 

        // Handle query
        try{
            // format values
            $formatValue  = self::formatDefaultValue($value);

            // Get the current column definition
            $stmt       = self::$pdo->query("DESCRIBE {$table} {$column}");
            $columnInfo = $stmt->execute()->fetch(PDO::FETCH_ASSOC);

            // Extract the column type, nullability, and constraints
            $columnType = $columnInfo['Type'];
            $isNullable = $columnInfo['Null'] === 'YES';
            $columnConstraints = $columnInfo['Extra'];
            
            // Generate the ALTER TABLE query to update the default value
            $query = "ALTER TABLE {$table} CHANGE {$column} {$column} {$columnType}";
            
            // Add nullability and constraints if applicable
            if(in_array($formatValue, ['null', 'none', 'not null', 'current_timestamp()'])){
                $query .= " " . strtoupper($formatValue);
            } else{
                $query .= " DEFAULT {$formatValue}";
            }

            // add Constraints if exixts
            if (!empty($columnConstraints)) {
                $query .= " {$columnConstraints}";
            }

            // execute query
            self::$pdo->query($query)->execute();

            return [
                'status'    => Constant::STATUS_200,
                'message'   => sprintf("Table `%s` has been altered. <br>\n %s", $table, $query),
            ];
        } catch (PDOException $e){
            return [
                'status'    => Constant::STATUS_404,
                'message'   => preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    sprintf("<<\\Error %s>> 
                        <br>
                        <<\\PDO::ERROR>> %s `%s` <br>\n 
                    ", Constant::STATUS_404, $e->getMessage(), $value)
                ),
            ];
        }
    }

    /**
     * Drop table
     * @param string $tableName 
     * @param bool $force 
     * 
     * @return array
     */
    public static function dropTable(?string $tableName, $force = false)
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
            if($force){
                self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0; "); // Disable foreign key checks temporarily
                self::$pdo->exec("DROP TABLE {$tableName} CASCADE;"); // Drop the table with CASCADE option
                self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;"); // Enable foreign key checks again
            } else{
                self::$pdo->query( "DROP TABLE {$tableName};" )->execute();
            }

            return [
                'status'    => Constant::STATUS_200,
                'message'   => "Table `{$tableName}` dropped successfully <br> \n",
            ];
        } catch (PDOException $e){
            return [
                'status'    => Constant::STATUS_404,
                'message'   => preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    sprintf("<<\\Error %s>> 
                        <br>
                        <<\\PDO::ERROR>> %s  \n
                    ", Constant::STATUS_404, $e->getMessage())
                ),
            ];
        }
    }

    /**
     * Drop column
     * @param string $tableName 
     * @param string $columnName 
     * 
     * @return array
     */
    public static function dropColumn(?string $tableName, ?string $columnName)
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
                'status'    => Constant::STATUS_404,
                'message'   => "Table column name cannot be empty. Please pass a value.<br>\n",
            ];
        }

        // Handle query
        try{
            // DROP COLUMN IF EXISTS
            self::$pdo->query( "ALTER TABLE {$tableName} DROP COLUMN {$columnName};" )->execute();

            // DROP COLUMN TRIGGERS 
            self::$pdo->query( "DROP TRIGGER IF EXISTS {$columnName}_created_at;" )->execute();

            // DROP COLUMN TRIGGERS 
            self::$pdo->query( "DROP TRIGGER IF EXISTS {$columnName}_updated_at;" )->execute();
            
            return [
                'status'    => Constant::STATUS_200,
                'message'   => "Column `{$columnName}` on `{$tableName}` dropped successfully <br> \n",
            ];
        } catch (PDOException $e){
            return [
                'status'    => Constant::STATUS_404,
                'message'   => preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    sprintf("<<\\Error %s>>
                        <br>
                        <<\\PDO::ERROR>> %s <br> \n
                    ", Constant::STATUS_404, $e->getMessage())
                ),
            ];
        }
    }

    /**
     * Format Default Value
     * @param mixed  $value
     * - The new default value for the column.
     * 
     * @return string
     */
    private static function formatDefaultValue(mixed $value = null)
    {
        // convert default values to string
        if(is_array($value)){
            $defaultValue = array_walk($defaultValue, function(&$value){
                return "'{$value}'";
            });
        } elseif(is_null($value)){
            $defaultValue = "null";
        } elseif(is_string($value)){
            $defaultValue = trim(strtolower((string) $value));
            if(in_array($defaultValue, ['null', 'none', 'not null', 'current_timestamp()'])){
                if($defaultValue === 'none'){
                    $defaultValue = "not null";
                }
                $defaultValue = strtolower("$defaultValue");
            } else{
                $defaultValue = "'$value'";
            }
        } 

        return $defaultValue;
    }

    /**
     * Check database connection error
     * 
     * @return mixed
     */
    private static function checkDBConnect()
    {
        $style = self::$style;

        // if database connection is okay
        $dbConnection = self::$db->dbConnection();
        if($dbConnection['status'] !== Constant::STATUS_200){
            return [
                'status'    => Constant::STATUS_404,
                'message'   => "Connection Error 
                                    <span style='background: #ee0707; {$style}'>
                                        Database Connection Error
                                    </span>
                                    `{$dbConnection['message']}` <br>\n",
            ];
        }
    }
    
}