<?php 

use builder\Database\DB;
use builder\Database\Env;
use builder\Database\Asset;
use builder\Database\DBImport;
use builder\Database\AutoLoader;
use builder\Database\AutoloadRegister;
use builder\Database\Migrations\Schema;
use builder\Database\Capsule\AppManager;
use builder\Database\Migrations\Migration;

if (! function_exists('autoloader_start')) {
    /**
     * Configure Instance of AutoLoader `Environment`
     * 
     * @param string $custom_path 
     * path \Path to .env file
     * - [optional] path \By default we use project root path
     * 
     * @return void
     */
    function autoloader_start(?string $custom_path = null)
    {
        (new AutoLoader)->start($custom_path);
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
        (new AutoloadRegister)->load($directory);
    }
}

if (! function_exists('config')) {
    /**
     * Get the value of a configuration option.
     *
     * @param string $key 
     * The configuration key in dot notation (e.g., 'database.connections.mysql')
     * 
     * @param mixed $default 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return mixed
     * The value of the configuration option, or null if it doesn't exist
     */
    function config(string $key, $default = null)
    {
        return (new AutoLoader)->config($key, $default);
    }
}

if (! function_exists('db')) {
    /**
     * Get Database 
     * 
     * @param string $key
     * - key of already defined connections
     * [dir]/config/database.php
     * 
     * @return \builder\Database\Connectors\Connector
     */
    function db(?string $key = 'default')
    {
        return DB::connection($key);
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
        return db()->dbConnection($type);
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
     * @param mixed $value
     * - [optional] Default value if key not found
     * 
     * @return mixed
     */
    function env(?string $key = null, mixed $value = null)
    {
        // Convert all keys to lowercase
        $envData = array_change_key_case($_ENV, CASE_UPPER);

        // convert to upper-case
        $key = strtoupper(trim((string) $key));

        return $envData[$key] ?? $value;
    }
}

if (! function_exists('env_update')) {
    /**
     * Update Environment [path .env] variables
     * 
     * @param string $key \Environment key you want to update
     * 
     * 
     * @param string|bool $value \Value of Variable to update
     * 
     * @param bool $allow_quote \Default is true
     * [optional] Allow quotes around values
     * 
     * @param bool $allow_space \Default is false
     * [optional] Allow space between key and value
     * 
     * @return bool
     */
    function env_update(?string $key = null, string|bool $value = null, ?bool $allow_quote = true, ?bool $allow_space = false)
    {
        return env_orm()->updateENV($key, $value, $allow_quote, $allow_space);
    }
}

if (! function_exists('env_orm')) {
    /**
     * Get Instance of Dot Env
     * 
     * @return \builder\Database\Env
     */
    function env_orm()
    {
        return (new Env);
    }
}

if (! function_exists('app_manager')) {
    /**
     * Get Instance of AppManager
     * 
     * @return \builder\Database\Capsule\AppManager
     */
    function app_manager()
    {
        return (new AppManager);
    }
}

if (! function_exists('import')) {
    /**
     * Database Importation
     * 
     * @param string $path_to_sql
     * 
     * @return object
     * [status, message]
     */
    function import($path_to_sql = null)
    {
        return (new DBImport)->import($path_to_sql);
    }
}

if (! function_exists('migration')) {
    /**
     * Get Instance of Migration
     * 
     * @return \builder\Database\Migration
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
     * @return \builder\Database\Migration\Schema
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
     * - buttons   | int                | Numbers of pagination links to generate. Default is 5 and limit is 20
     * 
     * @return void
     */
    function config_pagination(?array $options = [])
    {
        (new AutoLoader)->configPagination($options);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get Storage Directory `Path`
     * @param string $path
     * - [optional] You can pass a path to include with the base directory
     * - Final result: i.e C:/server_path/path
     * 
     * @return string
     */
    function storage_path(?string $path = null)
    {
        return base_path("storage/{$path}");
    }
}

if (! function_exists('config_path')) {
    /**
     * Get Config Directory `Path`
     * @param string $path
     * - [optional] You can pass a path to include with the base directory
     * - Final result: i.e C:/server_path/path
     * 
     * @return string
     */
    function config_path(?string $path = null)
    {
        return base_path("config/{$path}");
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
