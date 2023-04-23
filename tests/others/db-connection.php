<?php

use builder\Database\DB;

include_once __DIR__ . "/../vendor/autoload.php";

// this is all you need for connection
$model = new DB([
    'DB_USERNAME'   => '',
    'DB_PASSWORD'   => '',
    'DB_DATABASE'   => '',
]);


// All available settings
$model = new DB([
    'DB_HOST'       => '', //default value 'localhost'
    'DB_USERNAME'   => '', 
    'DB_PASSWORD'   => '', 
    'DB_DATABASE'   => '', 
    'DB_PORT'       => '', //default value '3306' for Mysql
    'DB_CHARSET'    => '', //default value 'utf8mb4_unicode_ci'
    'DB_COLLATION'  => '', //default value 'utf8mb4'
]);