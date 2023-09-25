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
     * Convert Model Class to table camel case
     * 
     * @return string|null
     */
    protected static function tabelCamelCase()
    {
        // check if child class is instance of Tamedevelopers\Database\Model
        if(self::isModelExtended()){
            // Convert camel case to snake case
            $snakeCase = Str::snakeCase(get_called_class());

            return Str::pluralize(
                strtolower($snakeCase)
            );
        }
    }

}