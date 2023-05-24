<?php

use builder\Database\EnvAutoLoad;

/*
|--------------------------------------------------------------------------
| Define global vairiables
|--------------------------------------------------------------------------
*/
global $dotEnv, $db;


/*
|--------------------------------------------------------------------------
| Include path to your autoloader
|--------------------------------------------------------------------------
*/
include_once __DIR__ . "/vendor/autoload.php";


/*
|--------------------------------------------------------------------------
| Start env configuration
| You can configura your pagination text data here if you like
|--------------------------------------------------------------------------
*/
EnvAutoLoad::start();


/*
|--------------------------------------------------------------------------
| Using autoload you now have access to two constant Data
|  DOT_ENV_CONNECTION
|--------------------------------------------------------------------------
*/
$dotEnv = DOT_ENV_CONNECTION;


/*
|--------------------------------------------------------------------------
| Using autoload you now have access to two constant Data
| DATABASE_CONNECTION 
|--------------------------------------------------------------------------
*/
$db = DATABASE_CONNECTION;


/*
|--------------------------------------------------------------------------
| Configure your pagination data here
| Should incase you are implementing language model from the database
| You can add get the text needed and add to the configuration as needed
| To override the Pagination settings below, use any of the Helper or Autoload methods
|
|   Helpers
|   config_pagination([])
|   or
|   EnvAutoLoad::configPagination()
|
|   When Helper function or EnvAutoLoad is used, you'll have access to CONSTANT NAME
|   `PAGINATION_CONFIG`
|
| key       | Data Type          |  Description                                                                           |
|-----------|--------------------|----------------------------------------------------------------------------------------|
| allow     | true\false         | Default `false` Setting to true will allow the system use this settings across app 
| class     | string             | Css `selector` For pagination ul tag in the browser 
| span      | string             | Css `selector` For pagination Showing Span tags in the browser 
| view      | bootstrap\simple   | Default `simple` - For pagination design 
| first     | string             | Change the letter of `First`
| last      | string             | Change the letter of `Last`
| next      | string             | Change the letter of `Next`
| prev      | string             | Change the letter of `Prev`
| showing   | string             | Change the letter of `Showing`
| of        | string             | Change the letter `of`
| results   | string             | Change the letter `results`
|--------------------------------------------------------------------------
*/
$db->configPagination([
   'allow'  => false,
   'view'   => 'simple',
]);


/*
|--------------------------------------------------------------------------
| To Disable/Enable Error Resporting
| Update the .env and set `APP_DEBUG` to TRUE|FALSE
| 
| Error report is saved into the below path to file
| path => \storage\logs\orm.log
|--------------------------------------------------------------------------
*/
$dotEnv['env']->errorLogger();