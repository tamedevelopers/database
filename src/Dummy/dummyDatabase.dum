<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | By default, the Database library supports only mysql.
    | `pgsql` support will be added soon
    | 
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | You can connect to multiple database connection, by default it uses
    | the mysql. We added an example reference as `woocommerce`. 
    | Just to show you how to setup additional connections.
    |
    |
    | All database is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'mysql' => [
            'driver'            => 'mysql',
            'host'              => env('DB_HOST', 'localhost'),
            'port'              => env('DB_PORT', '3306'),
            'database'          => env('DB_DATABASE', 'orm'),
            'username'          => env('DB_USERNAME', 'orm'),
            'password'          => env('DB_PASSWORD', ''),
            'charset'           => 'utf8mb4',
            'collation'         => 'utf8mb4_unicode_ci',
            'prefix'            => '',
            'prefix_indexes'    => false,
        ],

        
        'pgsql' => [
            'driver'            => 'pgsql',
            'url'               => env('DATABASE_URL'),
            'host'              => env('DB_HOST', '127.0.0.1'),
            'port'              => env('DB_PORT', '5432'),
            'database'          => env('DB_DATABASE', 'orm'),
            'username'          => env('DB_USERNAME', 'orm'),
            'password'          => env('DB_PASSWORD', ''),
            'charset'           => 'utf8',
            'prefix'            => '',
            'prefix_indexes'    => true,
            'search_path'       => 'public',
            'sslmode'           => 'prefer',
        ],

        'woocommerce' => [
            'driver'            => 'mysql',
            'host'              => env('WO_DB_HOST', 'localhost'),
            'port'              => env('WO_DB_PORT', '3306'),
            'database'          => env('WO_DB_DATABASE', 'orm'),
            'username'          => env('WO_DB_USERNAME', 'orm'),
            'password'          => env('WO_DB_PASSWORD', ''),
            'charset'           => 'utf8mb4',
            'collation'         => 'utf8mb4_unicode_ci',
            'prefix'            => env('WO_DB_PREFIX', 'wp_'),
            'prefix_indexes'    => true,
        ],  
    ],

];
