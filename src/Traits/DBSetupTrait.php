<?php

declare(strict_types=1);

namespace builder\Database\Traits;


use builder\Database\Connectors\Connector;

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
     * @return \builder\Database\Schema\Builder
     */
    public static function table(string $table)
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
    public static function tableExists(?string $table)
    {
        return (new Connector)->table('')->tableExists($table);
    }
    
}