<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Connectors\Traits;

use Exception;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Database\Connectors\MysqlConnector;
use Tamedevelopers\Database\Connectors\SQLiteConnector;
use Tamedevelopers\Database\Connectors\PostgresConnector;

trait ConnectorTrait{
    
    /**
     * Check if config key is isset and not empty
     *
     * @param mixed $config
     * @param string $key
     * @return bool
     */
    protected static function checkIssetEmpty($config, $key)
    {
        return isset($config[$key]) && !empty($config[$key]);
    }

    /**
     * Get supported database instance
     * @param string $driver
     * 
     * @return object
     */
    protected static function createConnector($driver = 'mysql')
    {
        return match ($driver) {
            'mysql'  => new MysqlConnector,
            'pgsql'  => new PostgresConnector,
            'sqlite' => new SQLiteConnector,
            default  => throw new Exception("Unsupported driver [{$driver}]."),
        };
    }

    /**
     * Check if Model Class is in use
     * 
     * @return bool
     */
    protected static function isModelExtended()
    {
        $childClassName     = get_called_class(); // Get the name of the child class
        $parentClassName    = get_parent_class($childClassName); // Get the name of the parent class
        if(is_subclass_of($childClassName, '\Tamedevelopers\Database\Model')){
            return true;
        }

        return false;
    }

    /**
     * Convert Model Class to table tabelPluralization name
     * 
     * @return string|null
     */
    protected static function tabelPluralization()
    {
        // check if child class is instance of Tamedevelopers\Database\Model
        if(self::isModelExtended()){
            return Str::pluralize(
                Str::snake(get_called_class())
            );
        }
    }

}