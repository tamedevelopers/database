<?php 

use builder\Database\DB;
use builder\Database\Asset;
use builder\Database\DBImport;
use builder\Database\EnvAutoLoad;
use builder\Database\Query\MySqlExec;
use builder\Database\AutoloadRegister;
use builder\Database\Schema\EnvOrm;
use builder\Database\Migrations\Schema;
use builder\Database\Migrations\Migration;

if (! function_exists('isDatabaseConnectionDefined')) {
    /**
     * Get Database Connection Constant Status
     * - When You connecto to Database using autoload or -- Direct connection
     * A Global Constant is Instantly defined for us.
     * This is to check if it has been defined or not
     * 
     * @return bool\builder\Database\isDatabaseConnectionDefined
     */
    function isDatabaseConnectionDefined()
    {
        return (new MySqlExec)->isDatabaseConnectionDefined();
    }
}

if (! function_exists('db')) {
    /**
     * Get Database 
     * 
     * @param array $options
     * - [optional] Database configuration options
     * 
     * @return object\builder\Database\DB
     */
    function db(?array $options = [])
    {
        return new DB($options);
    }
}

if (! function_exists('db_query')) {
    /**
     * Get Database Query
     * 
     * @return mixed
     */
    function db_query()
    {
        // get query
        return isDatabaseConnectionDefined() 
                ? DATABASE_CONNECTION->dbQuery()
                : (new MySqlExec)->dbQuery();
    }
}

if (! function_exists('db_config')) {
    /**
     * Database Configuration
     * 
     * @param array $options
     * - [optional] Database configuration options
     * - Same as `Direct DB Connection
     * 
     * @return void
     * - You now have access to a new Constant created for you
     * DATABASE_CONNECTION
     */
    function db_config(?array $options = [])
    {
        if ( ! isDatabaseConnectionDefined() ) {
            define('DATABASE_CONNECTION', db($options));
        }
    }
}

if (! function_exists('db_connection')) {
    /**
     * Get Database Connection
     * 
     * @param string $type
     * - [optional]  reponse|message|driver
     * 
     * @return mixed
     */
    function db_connection(?string $type = null)
    {
        // get database connection
        $connection = isDatabaseConnectionDefined() 
                    ? DATABASE_CONNECTION->dbConnection()
                    : db()->dbConnection();
        
        return $connection[$type] ?? (object) $connection;
    }
}

if (! function_exists('db_driver')) {
    /**
     * Get Database `PDO` Driver
     * 
     * @return mixed\builder\Database\getDriver
     */
    function db_driver()
    {
        // get database connection
        return db_connection('driver');
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

if (! function_exists('env')) {
    /**
     * Get ENV (Enviroment) Data
     * - If .env was not used, 
     * - Then it will get all App Configuration Data as well
     * 
     * @param string $key
     * - [optional] ENV KEY or APP Configuration Key
     * 
     * @return mixed
     */
    function env(?string $key = null)
    {
        // get Config data from ENV file
        $envData = (new MySqlExec)->env();

        // Convert all keys to lowercase
        $envData = array_change_key_case($envData, CASE_UPPER);

        // convert to upper-case
        $key = strtoupper(trim((string) $key));

        return $envData[$key] ?? $envData;
    }
}

if (! function_exists('env_orm')) {
    /**
     * Get Dot Env
     * 
     * @return object\builder\Database\Schema\EnvOrm
     */
    function env_orm()
    {
        return (new EnvOrm);
    }
}

if (! function_exists('env_start')) {
    /**
     * Configure Instance of EnvAutoLoad `Environment`
     * 
     * @param array $options
     * - [optional] path \You can specify custom project path
     * - By default path, is your project directory root
     * 
     * - [optional] dump_bg_color \(default | main | dark | red | blue)
     * 
     * @return void
     */
    function env_start(?array $options = [])
    {
        (new EnvAutoLoad)->start($options);
    }
}

if (! function_exists('migration')) {
    /**
     * Get Instance of Migration
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
     * Get Instance of Migration Schema
     * 
     * @return object\builder\Database\Migration\Schema
     */
    function schema()
    {
        return new Schema();
    }
}

if (! function_exists('asset')) {
    /**
     * Create assets Real path url
     * 
     * @param string $asset
     * - asset file e.g (style.css | js/main.js)
     * 
     * @return string
     */
    function asset(?string $asset = null)
    {
        return Asset::asset($asset);
    }
}

if (! function_exists('asset_config')) {
    /**
     * Configure Assets Default Directory
     * 
     * @param string $base_path
     * - [optional] Default is `base_directory/assets`
     * - If set and directory is not found, then we revert back to the default
     * 
     * @param string $cache
     * - [optional] Default is true
     * - End point of link `?v=xxxxxxxx` is with cache of file time change
     * - This will automatically tells the broswer to fetch new file if the time change
     * - Time will only change if you make changes or modify the request file
     * 
     * @return void
     */
    function asset_config(?string $base_path = null, ?bool $cache = true)
    {
        Asset::config($base_path, $cache);
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

if (! function_exists('config_pagination')) {
    /**
     * Configure Pagination
     * 
     * @param array $options
     * - [optional] keys
     * 
     * - allow     | true\false         | Default `false` Setting to true will allow the system use this settings across app 
     * - class     | string             | Css `selector` For pagination ul tag in the browser 
     * - span      | string             | Css `selector` For pagination Showing Span tags in the browser 
     * - view      | bootstrap\simple   | Default `simple` - For pagination design 
     * - first     | string             | Change the letter of `First`
     * - last      | string             | Change the letter of `Last`
     * - next      | string             | Change the letter of `Next`
     * - prev      | string             | Change the letter of `Prev`
     * - showing   | string             | Change the letter of `Showing`
     * - of        | string             | Change the letter `of`
     * - results   | string             | Change the letter `results`
     * 
     * @return void
     */
    function config_pagination(?array $options = [])
    {
        (new EnvAutoLoad)->configPagination($options);
    }
}

if (! function_exists('directory')) {
    /**
     * Get Base Directory `Path`
     * @param string $path
     * - [optional] You can pass a path to include with the base directory
     * - Final result: i.e C:/server_path/path
     * 
     * @return string
     */
    function directory(?string $path = null)
    {
        return base_path($path);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get Base Directory `Path`
     * @param string $path
     * - [optional] You can pass a path to include with the base directory
     * - Final result: i.e C:/server_path/path
     * 
     * @return string
     */
    function base_path(?string $path = null)
    {
        return env_orm()->formatWithBaseDirectory($path);
    }
}

if (! function_exists('domain')) {
    /**
     * Get Domain `URL` URI
     * 
     * @param string $path
     * - [optional] You can pass a path to include with the domain link
     * - Final result: i.e https://domain.com/path
     * 
     * @return string
     */
    function domain(?string $path = null)
    {
        return env_orm()->formatWithDomainURI($path);
    }
}

if (! function_exists('app_data')) {
    /**
     * Get All Application Data
     * 
     * - Array of
     * - [keys] path|database|pagination
     * 
     * @return array
     */
    function app_data()
    {
        // get base root path
        $getPath = defined('DOT_ENV_CONNECTION') 
                    ? DOT_ENV_CONNECTION['server']
                    : env_orm()->getDirectory();

        // get database
        $database = isDatabaseConnectionDefined()  
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

if (! function_exists('dump')) {
    /**
     * Dump Data
     * @param mixed $data
     * 
     * @return void
     */ 
    function dump(...$data)
    {
        env_orm()->dump($data);
    }
}

if (! function_exists('dd')) {
    /**
     * Dump and Data
     * @param mixed $data
     * 
     * @return void
     */ 
    function dd(...$data)
    {
        env_orm()->dump($data);
        exit(1);
    }
}
