<?php 

use builder\Database\DB;
use builder\Database\DBImport;
use builder\Database\AutoloadEnv;
use builder\Database\Query\MySqlExec;
use builder\Database\AutoloadRegister;
use builder\Database\Schema\OrmDotEnv;
use builder\Database\Migrations\Schema;
use builder\Database\Migrations\Migration;

if (! function_exists('db')) {
    /**
     * Get Database 
     * @param array $options
     * 
     * @return object\builder\Database\DB
     */
    function db(?array $options = [])
    {
        return new DB($options);
    }
}

if (! function_exists('import')) {
    /**
     * Get Database Import Instance
     * 
     * @return object\builder\Database\DBImport
     */
    function import()
    {
        return new DBImport();
    }
}

if (! function_exists('dot_env')) {
    /**
     * Get Dot Env
     * 
     * @return object\builder\Database\Schema\OrmDotEnv
     */
    function dot_env()
    {
        return new OrmDotEnv();
    }
}

if (! function_exists('migration')) {
    /**
     * Get Migration Helpers
     * 
     * @return object\builder\Database\Migration
     */
    function migration()
    {
        return new Migration();
    }
}

if (! function_exists('schema')) {
    /**
     * Get Migration Helpers
     * 
     * @return object\builder\Database\Migration
     */
    function schema()
    {
        return new Schema();
    }
}

if (! function_exists('autoload_register')) {
    /**
     * Autoload function to load class and files in a given folder
     *
     * @param string|array $baseDirectory 
     * - The directory path to load
     * - Do not include the root path, as The Application already have a copy of your path
     * - e.g 'classes' or ['app/main', 'includes']
     * 
     * @return void\builder\Database\AutoloadRegister
     */
    function autoload_register(string|array $directory)
    {
        return (new AutoloadRegister)::load($directory);
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
        return new AutoloadEnv();
    }
}

if (! function_exists('db_exec')) {
    /**
     * Get MySqlExec
     * 
     * @return object\builder\Database\Query\MySqlExec
     */
    function db_exec()
    {
        return new MySqlExec();
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
        return db_exec()->dump($data);
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
            define('DATABASE_CONNECTION', db($options));
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
        return dot_env()->getDirectory();
    }
}

if (! function_exists('app_config')) {
    /**
     * Get App Configuration
     * @param string $key
     * 
     * @return mixed
     */
    function app_config(?string $key = null)
    {
        // get Config data from ENV file
        $AppConfig = db_exec()->AppConfig();

        // Convert all keys to lowercase
        $AppConfig = array_change_key_case($AppConfig, CASE_UPPER);

        // convert to upper-case
        $key = strtoupper(trim((string) $key));

        return $AppConfig[$key] ?? $AppConfig;
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
                    : db()->getConnection($type);

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
                    : dot_env()->getDirectory();

        // get database
        $database = defined('DATABASE_CONNECTION') 
                    ? DATABASE_CONNECTION
                    : db();

        // get pagination
        $pagination = defined('PAGINATION_CONFIG') 
                    && PAGINATION_CONFIG['allow'] === true
                    ? PAGINATION_CONFIG
                    : db()->pagination_settings;

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
                    : db_exec()->getQuery();
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
