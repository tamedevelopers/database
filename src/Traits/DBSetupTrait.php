<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;


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
        return (new Connector)->table($table);
    }

    /**
     * Check if table exists
     * 
     * @param string $table
     * 
     * @return bool
     */
    public static function tableExists($table)
    {
        return (new Connector)->table('')->tableExists($table);
    }
    
}