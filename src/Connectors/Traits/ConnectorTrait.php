<?php

declare(strict_types=1);

namespace builder\Database\Connectors\Traits;

use Exception;
use builder\Database\Capsule\Str;
use builder\Database\Connectors\MysqlConnector;
use builder\Database\Connectors\SQLiteConnector;
use builder\Database\Connectors\PostgresConnector;

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
        if(is_subclass_of($childClassName, '\builder\Database\Model')){
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
        // check if child class is instance of builder\Database\Model
        if(self::isModelExtended()){
            // Convert camel case to snake case
            $snakeCase = Str::snakeCase(get_called_class());

            return Str::pluralize(
                strtolower($snakeCase)
            );
        }
    }

}