<?php 

use builder\Database\DB;
use builder\Database\DBImport;
use builder\Database\AutoloadEnv;
use builder\Database\Query\MySqlExec;
use builder\Database\Schema\OrmDotEnv;
use builder\Database\Migrations\Migration;


if (! function_exists('orm_db')) {
    /**
     * Get Database 
     * @param array $options
     * 
     * @return object\builder\Database\DB
     */
    function orm_db(?array $options = [])
    {
        return new DB($options);
    }
}

if (! function_exists('orm_import')) {
    /**
     * Get Database Import Instance
     * 
     * @return object\builder\Database\DBImport
     */
    function orm_import()
    {
        return (new DBImport);
    }
}

if (! function_exists('orm_dot_env')) {
    /**
     * Get ORM Dot Env
     * 
     * @return object\builder\Database\Schema\OrmDotEnv
     */
    function orm_dot_env()
    {
        return (new OrmDotEnv);
    }
}

if (! function_exists('orm_migration')) {
    /**
     * Get Migration Helpers
     * 
     * @return object\builder\Database\Migration
     */
    function orm_migration()
    {
        return (new Migration);
    }
}

if (! function_exists('autoload_env')) {
    /**
     * Get Autoload Env
     * 
     * @return object\builder\Database\AutoloadEnv
     */
    function autoload_env()
    {
        return (new AutoloadEnv);
    }
}

if (! function_exists('get_orm_db_exec')) {
    /**
     * Get MySqlExec
     * 
     * @return object\builder\Database\Query\MySqlExec
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

if (! function_exists('env_start')) {
    /**
     * Configure Environment Start
     * @param array $options
     * 
     * @return mixed
     */
    function env_start(?array $options = [])
    {
        autoload_env()->start($options);
    }
}

if (! function_exists('config_database')) {
    /**
     * Configure Database
     * 
     * @return mixed
     */
    function config_database(?array $options = [])
    {
        if ( ! defined('DATABASE_CONNECTION') ) {
            define('DATABASE_CONNECTION', orm_db($options));
        }
    }
}

if (! function_exists('configure_pagination')) {
    /**
     * Configure Pagination
     * @param array $options
     * 
     * @return mixed
     */
    function configure_pagination(?array $options = [])
    {
        autoload_env()->configurePagination($options);
    }
}

if (! function_exists('base_dir')) {
    /**
     * Get Base Directory
     * 
     * @return string
     */
    function base_dir()
    {
        return orm_dot_env()->getDirectory();
    }
}

if (! function_exists('app_config')) {
    /**
     * Get App Configuration
     * 
     * @return mixed
     */
    function app_config()
    {
        return get_orm_db_exec()->AppConfig();
    }
}

if (! function_exists('get_connection')) {
    /**
     * Get Database Connection
     * @param string $type\reponse|message|driver
     * 
     * @return mixed
     */
    function get_connection(?string $type = null)
    {
        // get database connection
        $connection = defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION->getConnection($type)
                    : orm_db()->getConnection($type);

        return (object) $connection;
    }
}

if (! function_exists('get_app_data')) {
    /**
     * Get All Application Data
     * 
     * @return array
     */
    function get_app_data()
    {
        // get base root path
        $getPath = defined('DOT_ENV_CONNECTION') 
                    ? DOT_ENV_CONNECTION['self_path']['path'] 
                    : orm_dot_env()->getDirectory();

        // get database
        $database = defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION
                    : orm_db();

        // get pagination
        $pagination = defined('PAGINATION_CONFIG') 
                    && PAGINATION_CONFIG['allow'] === true
                    ? PAGINATION_CONFIG
                    : orm_db()->pagination_settings;

        return [
            'path'          => $getPath,
            'database'      => $database,
            'pagination'    => $pagination,
        ];
    }
}

if (! function_exists('get_query')) {
    /**
     * Get Database Query
     * 
     * @return mixed
     */
    function get_query()
    {
        // get query
        return defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION->getQuery()
                    : get_orm_db_exec()->getQuery();
    }
}

if (! function_exists('to_array')) {
    /**
     * Convert data to array
     * @param mixed $items
     * 
     * @return array
     */ 
    function to_array(mixed $items)
    {
        return json_decode( json_encode($items), true);
    }
}

if (! function_exists('to_object')) {
    /**
     * Convert data to object
     * @param mixed $items
     * 
     * @return mixed
     */ 
    function to_object(mixed $items)
    {
        return json_decode( json_encode($items), false);
    }
}

if (! function_exists('to_json')) {
    /**
     * Convert data to json
     * @param mixed $items
     * 
     * @return mixed
     */ 
    function to_json(mixed $items)
    {
        return json_encode( $items );
    }
}
