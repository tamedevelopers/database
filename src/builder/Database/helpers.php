<?php 

use builder\Database\DB;
use builder\Database\AutoloadEnv;
use builder\Database\Query\MySqlExec;
use builder\Database\Schema\OrmDotEnv;


if (! function_exists('get_orm_db_exec')) {
    /**
     * Get Object Execution Object of MySQL
     * 
     * @return object\builder\Database\get_orm_db_exec
     */
    function get_orm_db_exec()
    {
        return (new MySqlExec);
    }
}

if (! function_exists('ddump')) {
    /**
     * Format query data to browser
     * @param mixed ...$data
     * 
     * @return mixed
     */
    function ddump(...$data)
    {
        return get_orm_db_exec()->dump($data);
    }
}

if (! function_exists('envStart')) {
    /**
     * Configure envStart
     * 
     * @return mixed
     */
    function envStart()
    {
        AutoloadEnv::start();
    }
}

if (! function_exists('ConfigDatabase')) {
    /**
     * Configure ConfigDatabase
     * 
     * @return mixed
     */
    function ConfigDatabase(?array $options = [])
    {
        if ( ! defined('DATABASE_CONNECTION') ) {
            define('DATABASE_CONNECTION', new DB($options));
        }
    }
}

if (! function_exists('configurePagination')) {
    /**
     * Configure configurePagination
     * @param array $options
     * 
     * @return mixed
     */
    function configurePagination(?array $options = [])
    {
        AutoloadEnv::configurePagination($options);
    }
}

if (! function_exists('AppConfig')) {
    /**
     * Get App Configuration
     * 
     * @return mixed
     */
    function AppConfig()
    {
        return get_orm_db_exec()->AppConfig();
    }
}

if (! function_exists('GetConnection')) {
    /**
     * Get Database Connection
     * @param string $type\reponse|message|driver
     * 
     * @return mixed
     */
    function GetConnection(?string $type = null)
    {
        // get database connection
        $connection = defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION->getConnection($type)
                    : (new DB())->getConnection($type);
            
               
        dump(
            (new DB())->getConnection($type)
        );
        exit();
        return (object) $connection;
    }
}

if (! function_exists('getAppData')) {
    /**
     * Get All Application Data
     * 
     * @return array
     */
    function getAppData()
    {
        // get base root path
        $getPath = defined('DOT_ENV_CONNECTION') 
                    ? DOT_ENV_CONNECTION['self_path']['path'] 
                    : (new OrmDotEnv)->getDirectory();

        // get database
        $database = defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION
                    : new DB();

        // get pagination
        $pagination = defined('PAGINATION_CONFIG') 
                    && PAGINATION_CONFIG['allow'] === true
                    ? PAGINATION_CONFIG
                    : (new DB)->pagination_settings;

        return [
            'path'          => $getPath,
            'database'      => $database,
            'pagination'    => $pagination,
        ];
    }
}

if (! function_exists('getQuery')) {
    /**
     * Get Database Query
     * 
     * @return mixed
     */
    function getQuery()
    {
        // get query
        return defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION->getQuery()
                    : get_orm_db_exec()->getQuery();
    }
}

if (! function_exists('toArray')) {
    /**
     * Convert data to array
     * @param mixed $data
     * 
     * @return array
     */ 
    function toArray(mixed $data)
    {
        return get_orm_db_exec()->toArray($data);
    }
}

if (! function_exists('toObject')) {
    /**
     * Convert data to object
     * @param mixed $data
     * 
     * @return mixed
     */ 
    function toObject(mixed $data)
    {
        return get_orm_db_exec()->toObject($data);
    }
}

if (! function_exists('toJson')) {
    /**
     * Convert data to json
     * @param mixed $data
     * 
     * @return mixed
     */ 
    function toJson(mixed $data)
    {
        return get_orm_db_exec()->toJson($data);
    }
}
