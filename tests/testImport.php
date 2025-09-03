<?php
    
use Tamedevelopers\Database\DBImport;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


$import = new DBImport(base_path('tests/database/orm.sql'), 'woocommerce');

// do this 
$response = $import->run();

// of function helper
// 'woocommerce' is the database connection name you want to use
// $same = import('orm.sql', 'woocommerce');
// $same->run()


dump( 
    $response
);

?>
