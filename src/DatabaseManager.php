<?php

declare(strict_types=1);

namespace builder\Database;

use builder\Database\Capsule\FileCache;
use builder\Database\Connectors\Connector;
use builder\Database\Traits\ReusableTrait;


class DatabaseManager extends DatabaseConnector {

    use ReusableTrait;
    
    /**
     * Database Storage Cache Key
     * This allow us ability to store multiple connections on each instance
     * @var string
     */
    const CONNECTION_CACHE = "database.connections.";
    

    /**
     * Connect to a Database 
     * 
     * @param string $name
     * - [name] of connections in [config/database.php] file
     * 
     * @param array $default 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return $this
     */
    public static function connection(?string $name = null, ?array $default = [])
    {
        $config = self::driverValidator($name);
        if (!FileCache::has($config['key'])) {
            // create data
            $data = self::getDriverData(
                config("database.connections.{$config['name']}")
            );

            // merge data
            $mergeData = array_merge($data ?? [], $default ?? []);
            
            // Cache the connection
            FileCache::put(
                $config['key'], 
                self::createDriverData($mergeData)
            );
        }

        return new Connector($config['name']);
    }

    /**
     * Get Connection data
     * 
     * @param string $name
     * - [name] of connections\Default name is `default`
     * 
     * @return mixed
     */
    public static function getConnection(?string $name = null)
    {
        $key = self::getCacheKey($name);
        if (FileCache::has($key)) {
            return FileCache::get($key);
        }

        return [];
    }

    /**
     * Disconnect from a database.
     *
     * @param string|null $name
     * @return void
     */
    public static function disconnect(?string $name = null)
    {
        $name = empty($name) ? 'default' : $name;
        $key  = self::getCacheKey($name);
        if (FileCache::has($key)) {
            FileCache::forget($key);
        }
    }

    /**
     * Reconnect to a database.
     *
     * @param string|null $name
     * 
     * * @param mixed $default 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return object
     */
    public static function reconnect(?string $name = null, mixed $default = null)
    {
        return self::connection($name, $default);
    }

    /**
     * get Cache Key name
     *
     * @param string $name
     * @return string
     */
    public static function getCacheKey($name = null)
    {
        return self::CONNECTION_CACHE . $name;
    }

    /**
     * get Cache Key name
     *
     * @param string $name
     * @return array
     */
    private static function driverValidator(?string $name = null)
    {
        $name = self::getDriverName($name);
        return [
            'name'  => $name,
            'key'   => self::getCacheKey($name),
        ];
    }
    
}
