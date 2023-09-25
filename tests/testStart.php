<?php
    
use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";


// run
autoloader_start();

// or

// start env configuration
AutoLoader::start();


// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


// running any of the above code will auto setup your entire application.
// you can remove line of codes when done

dd(
    'Working',
    DB::table('wallet')->get()
);