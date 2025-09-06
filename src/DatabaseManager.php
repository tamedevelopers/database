<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Database\Connectors\Connector;


class DatabaseManager extends DatabaseConnector {

    /**
     * Database Storage Key
     * This allow us ability to store multiple connections on each instance
     * @var string
     */
    const CONNECTION_KEY = "database.connections.";

    /**
     * Connect to a Database 
     * 
     * @param string|null $name
     * - [name] of connections in [config/database.php] file
     * 
     * @param array $options 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return \Tamedevelopers\Database\Connectors\Connector
     */
    public static function connection($name = null, $options = [])
    {
        // Ensure environment variables are loaded before accessing them
        Manager::startEnvIFNotStarted();
        
        [$name, $options] = self::prepareValues(
            $name, $options, func_num_args() === 2
        );
        
        // connector object
        return Connector::addConnection(
            name: $name,
            data: $options,
        );
    }

    /**
     * Prepare Values
     *
     * @param  string|null $name
     * @param  array $default
     * @param  bool $useDefault
     * @return void
     */
    private static function prepareValues($name = null, $default = [], $useDefault = false)
    {
        // when only one data is passed 
        // now we just check if data is an array
        if(!$useDefault && is_array($name)){
            return [null, $name];
        }

        return [$name, $default];
    }

    /**
     * Reconnect to a database.
     *
     * @param string|null $name
     * 
     * * @param array $options 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return \Tamedevelopers\Database\Connectors\Connector
     */
    public static function reconnect($name = null, $options = [])
    {
        return self::connection($name, $options);
    }

    /**
     * Disconnect from a database.
     *
     * @param string|null $name
     * @return void
     */
    public static function disconnect($name = null)
    {
        Connector::removeFromConnection($name);
    }

    /**
     * Get Connection Key
     *
     * @param string $name
     * @return string
     */
    public static function getConnectionKey($name = null)
    {
        return self::CONNECTION_KEY . $name;
    }

    /**
     * get Cache Key name
     *
     * @param string|null $name
     * @return array
     */
    public static function driverValidator($name = null)
    {
        $name = self::getDriverName($name);
        return [
            'name'  => $name,
            'key'   => self::getConnectionKey($name),
        ];
    }
    
}
