<?php

declare(strict_types=1);

namespace builder\Database;

use builder\Database\Capsule\Str;

/**
 * @property-read mixed $table
 */
class DatabaseConnector{

    /**
     * Find Database Connection data
     * @param mixed $data
     * @return array
     */
    protected static function getDriverData($data = null)
    {
        if(!is_array($data)){
            $default = config("database.default");
            return config(
                "database.connections.{$default}"
            );
        }

        return $data;
    }

    /**
     * Find Database Driver Name
     * @param string $name
     * @return string
     */
    protected static function getDriverName(?string $name = null)
    {
        // try to get driver config data
        $config = config(
            "database.connections.{$name}"
        );
        return empty($config) ? 'default' : $name;
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
     * @param string $driver
     * 
     * @return string|null
     */
    public static function findDriver(?string $driver = null)
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
     * @param string $collation
     * 
     * @return string|null
     */
    public static function findCollation(?string $collation = null)
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
     * @param string $charset
     * 
     * @return string|null
     */
    public static function findCharset(?string $charset = null)
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
        return ['utf8mb4_unicode_ci', 'utf8mb4_general_ci', 'utf8mb4_bin', 'utf8_general_ci', 'utf8_bin', 'latin1_general_ci', 'latin1_bin',];
    }
    
}



