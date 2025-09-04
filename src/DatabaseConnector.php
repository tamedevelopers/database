<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Server;

/**
 * @property-read mixed $table
 */
class DatabaseConnector{

    /**
     * Find Database Connection data
     * @param array|null $data
     * @return array
     */
    protected static function getDriverData($data = null)
    {
        if(!is_array($data)){
            $default = self::getDriverName();
            return Server::config(
                "database.connections.{$default}",
                []
            );
        }

        return $data;
    }

    /**
     * Find Database Driver Name
     * @param string|null $name
     * @return string
     */
    protected static function getDriverName($name = null)
    {
        if(empty($name)){
            return Server::config("database.default");
        }

        // try to get driver config data
        $database = Server::config("database.connections.{$name}");

        return empty($database) 
                ? Server::config("database.default") 
                : $name;
    }

    /**
     * Construct needed Database Driver data
     * 
     * @param array $options
     * 
     * @return array
     */
    protected static function createDriverData(?array $options = [])
    {
        $defaultOption = array_merge([
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'port'          => 3306,
            'database'      => '',
            'username'      => '',
            'password'      => '',
            'charset'       => 'utf8mb4',
            'collation'     => 'utf8mb4_unicode_ci',
            'prefix'        => '',
            'prefix_indexes'=> false,
        ], $options ?? []);

        // get accepted data
        $defaultOption['collation']  = self::findCollation($defaultOption['collation']);
        $defaultOption['charset']    = self::findCharset($defaultOption['charset']);
        $defaultOption['driver']     = self::findDriver($defaultOption['driver']);
        
        return $defaultOption;
    }

    /**
     * Get supported database Driver
     * @param string|null $driver
     * 
     * @return string|null
     */
    protected static function findDriver($driver = null)
    {
        // collation get
        $driver = Str::lower($driver);
        if(!in_array($driver, self::supportedDrivers())){
            return self::supportedDrivers()[1]; 
        }

        return $driver; 
    }

    /**
     * Get supported database Collation
     * @param string|null $collation
     * 
     * @return string|null
     */
    protected static function findCollation($collation = null)
    {
        // collation get
        $collation = Str::lower($collation);
        if(!in_array($collation, self::supportedCollations())){
            return self::supportedCollations()[1]; 
        }

        return $collation; 
    }

    /**
     * Get supported database Charset
     * @param string|null $charset
     * 
     * @return string|null
     */
    protected static function findCharset($charset = null)
    {
        // charset get
        $charset = Str::lower($charset);
        if(!in_array($charset, self::supportedCharsets())){
            return self::supportedCharsets()[0]; 
        }

        return $charset; 
    }
    
    /**
     * Get all of the support drivers.
     *
     * @return array
     */
    private static function supportedDrivers()
    {
        // ['mysql', 'pgsql', 'sqlite']
        return ['mysql', 'pgsql'];
    }

    /**
     * Get all of the support Charsets.
     *
     * @return array
     */
    private static function supportedCharsets()
    {
        return ['utf8mb4', 'utf8', 'latin1'];
    }

    /**
     * Get all of the support Charsets.
     *
     * @return array
     */
    private static function supportedCollations()
    {
        return ['utf8mb4_unicode_ci', 'utf8mb4_general_ci', 'utf8mb4_bin', 'utf8_general_ci', 'utf8_bin', 'latin1_general_ci', 'latin1_bin'];
    }
    
}
