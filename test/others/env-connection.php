<?php

use UltimateOrmDatabase\Methods\OrmDotEnv;

include_once __DIR__ . "/../vendor/autoload.php";

// to use .env environment variable this has to be called before the DB Model
$dotenv = new OrmDotEnv('PATH_TO_ENV_FOLDER');
$dotenv->load();

// or 

// to use .env environment variable this has to be called before the DB Model
$dotenv = new OrmDotEnv('PATH_TO_ENV_FOLDER');
$dotenv->loadOrFail();



// or static call
OrmDotEnv::load('PATH_TO_ENV_FOLDER');

// or static call
// This will stop entire code from running if there's none .env file found in the directory folder
// use on Development only, as env path finder should work fine on Production after setting it up 
OrmDotEnv::loadOrFail('PATH_TO_ENV_FOLDER');


// in php you can access environment variable using 
// $_ENV or $_SERVER
// avoid using $_SERVER as it will load every SERVER variable including the environments as well.
// $_ENV['DB_PASSWORD']


// Once environment variable has been loaded, then you can start using the DB without anymore configuration
