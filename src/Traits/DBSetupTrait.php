<?php

declare(strict_types=1);

namespace builder\Database\Traits;


use builder\Database\Capsule\Manager;
use builder\Database\Connectors\Connector;

/**
 * @property static $staticConn
 */
trait DBSetupTrait{

    /**
     * Configuring pagination settings 
     * @param array $options
     * 
     * @return void
     */
    private static function initConfiguration(array $options) 
    {
        // Only if the Global Constant is not yet defined
        if ( ! defined('PAGINATION_CONFIG') ) {
            self::configPagination($options);
        } else{
            // If set to allow global use of ENV Autoloader Settings
            if(Manager::isEnvBool(PAGINATION_CONFIG['allow']) === true){
                self::configPagination(PAGINATION_CONFIG);
            } else{
                self::configPagination($options);
            }
        }
    }

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