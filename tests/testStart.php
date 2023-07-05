<?php
    
use builder\Database\AutoLoader;

include_once __DIR__ . "/../vendor/autoload.php";


// run
autoloader_start();

// or

// start env configuration
AutoLoader::start();


// running any of the above code will auto setup your entire application.
// you can remove line of codes when done