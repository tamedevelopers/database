<?php
    
use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";


// run
// autoloader_start();
// or -- start env configuration
AutoLoader::start();


// Calling the Bootler is now [optional]
// handles pretty error displays on browser as well
AppManager::bootLoader();


// running any of the above code will auto setup your entire application.
// you can remove line of codes when done

dd(
    'Working',
    DB::table('wallet')->first(),
    DB::table('wallet')->get(),
);