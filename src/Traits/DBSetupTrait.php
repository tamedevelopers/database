<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;


use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Database\Connectors\Connector;

/**
 * @property static $staticConn
 */
trait DBSetupTrait{

    /**
     * Table name
     * This is being used on all instance of one query
     * 
     * @param string $table
     * 
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    public static function table($table)
    {
        return self::connector()->table($table);
    }

    /**
     * Check if table exists
     * 
     * @param mixed $table
     * 
     * @return bool
     */
    public static function tableExists(...$table)
    {
        return self::connector()->table('')->tableExists($table);
    }

    /**
     * Direct Query Expression
     *
     * @param string $query
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    public static function query(string $query)
    {
        return self::connector()->query($query);
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @param  string|null  $as
     * 
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    public static function from($table, $as = null)
    {
        return self::table('')->from($table, $as);
    }
    
    /**
     * Run a SELECT query and return all results.
     *
     * @param string $query
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public static function select(string $query)
    {
        return self::query($query)->get();
    }
    
    /**
     * Run a SELECT query and return a single result.
     *
     * @param string $query
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public static function selectOne(string $query)
    {
        return self::query($query)->first();
    }

    /**
     * Get Connector instance
     * 
     * @return \Tamedevelopers\Database\Connectors\Connector
     */
    private static function connector()
    {
        // Ensure environment variables are loaded before accessing them
        Manager::startEnvIFNotStarted();

        $connector = new Connector();

        // automatically connect to database when model is instantiated
        $connector->connection();

        return $connector;
    }
    
}