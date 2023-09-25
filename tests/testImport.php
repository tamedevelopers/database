<?php
    
use Tamedevelopers\Database\DBImport;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


$import = new DBImport();


// do this 
$response = $import->import('orm.sql');

// of function helper
$same = import('orm.sql');


dump( 
    $response['message'] 
);

?>
