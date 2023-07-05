<?php

use builder\Database\Capsule\AppManager;

/*
|--------------------------------------------------------------------------
| Define global vairiables
|--------------------------------------------------------------------------
*/
global $db;


/*
|--------------------------------------------------------------------------
| Include path to your autoloader
|--------------------------------------------------------------------------
*/
include_once __DIR__ . "/vendor/autoload.php";


/*
|--------------------------------------------------------------------------
| Booting application
|--------------------------------------------------------------------------
*/
AppManager::bootLoader();


/*
|--------------------------------------------------------------------------
| Default connection type is `mysql`
| So you can use the $db | db()
| in your application
|--------------------------------------------------------------------------
*/
$db = db();


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
|   AutoLoader::configPagination()
|
| key       | Data Type          |  Description                                                                           |
|-----------|--------------------|----------------------------------------------------------------------------------------|
| allow     | true\false         | Default `false` Setting to true will allow the system use this settings across app 
| class     | string             | Css `selector` For pagination ul tag in the browser 
| span      | string             | Css `selector` For pagination Showing Span tags in the browser 
| view      | bootstrap\simple\cursor   | Default `simple` - For pagination design 
| first     | string             | Change the letter of `First`
| last      | string             | Change the letter of `Last`
| next      | string             | Change the letter of `Next`
| prev      | string             | Change the letter of `Prev`
| showing   | string             | Change the letter of `Showing`
| of        | string             | Change the letter `of`
| results   | string             | Change the letter `results`
| buttons   | int                | Numbers of pagination links to generate. Default is 5 and limit is 20
|--------------------------------------------------------------------------
*/
$db->configPagination([
   'allow'  => false,
   'view'   => 'simple',
]);


/*
|--------------------------------------------------------------------------
| Configure Assets Default Directory
|
| @param string $base_path
| [optional] Default has been set to `/` which is your project base directory
|
| @param string $cache
| [optional] Default is true
| The Cache when true, works like a PWA application, that automatically
| expire old cache whenever a change has been made to the file
|--------------------------------------------------------------------------
*/
asset_config('/', true);