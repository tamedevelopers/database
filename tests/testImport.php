<?php
    
use builder\Database\DBImport;
use builder\Database\AutoLoader;

include_once __DIR__ . "/../vendor/autoload.php";

// start env configuration
AutoLoader::start();




$import = new DBImport();


// do this 
$response = $import->import('orm.sql');

// of function helper
$same = import('orm.sql');


$import->dump( 
    $response['message'] 
);

?>
