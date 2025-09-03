<?php 

use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\DBExport;
use Tamedevelopers\Database\DBImport;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Database\Migrations\Schema;
use Tamedevelopers\Database\Capsule\AppManager;
use Tamedevelopers\Database\Migrations\Migration;

if (! function_exists('autoloader_start')) {
    /**
     * Configure Instance of AutoLoader `Environment`
     * 
     * @param string|null $custom_path 
     * path \Path to .env file
     * - [optional] path \By default we use project root path
     * 
     * @return void
     */
    function autoloader_start($custom_path = null)
    {
        (new AutoLoader)->start($custom_path);
    }
}

if (! function_exists('db')) {
    /**
     * Get Database 
     * 
     * @param string|null $name
     * - [name] of connections in [config/database.php] file
     * 
     * @param array $default 
     * [optional] The default value to return if the configuration option is not found
     * 
     * @return \Tamedevelopers\Database\Connectors\Connector
     */
    function db($name = null, $default = [])
    {
        return DB::connection($name, $default);
    }
}

if (! function_exists('db_connection')) {
    /**
     * Get Database Connection
     * 
     * @param string|null $type
     * - [optional]  reponse|message|driver
     * 
     * @param string|null $name
     * - [name] of connections in [config/database.php] file
     * 
     * @return mixed
     */
    function db_connection($type = null, $name = null)
    {
        return db($name)->dbConnection($type);
    }
}

if (! function_exists('app_manager')) {
    /**
     * Get Instance of AppManager
     * 
     * @return \Tamedevelopers\Database\Capsule\AppManager
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
     * @param string $path
     * @param string|null $connection
     * 
     * @return \Tamedevelopers\Database\DBImport
     */
    function import($path, $connection = null)
    {
        return new DBImport($path, $connection);
    }
}

if (! function_exists('export')) {
    /**
     * Database Exportation
     * 
     * @param string|null $saveAsFileType — Save the backup file as [zip|rar]
     * @param string|null $connection
     * @param int $retentionDays — Days to keep backups before deletion
     * 
     * @return \Tamedevelopers\Database\DBExport
     */
    function export($saveAsFileType = null, $connection = 'default', int $retentionDays = 7)
    {
        return new DBExport($saveAsFileType, $connection, $retentionDays);
    }
}

if (! function_exists('migration')) {
    /**
     * Get Instance of Migration
     * 
     * @return \Tamedevelopers\Database\Migration
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
     * @return \Tamedevelopers\Database\Migration\Schema
     */
    function schema()
    {
        return new Schema();
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
