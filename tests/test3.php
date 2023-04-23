<?php
    
use builder\Database\DBImport;
use builder\Database\AutoloadEnv;

include_once __DIR__ . "/../vendor/autoload.php";

// start env configuration
AutoloadEnv::start();




$import = new DBImport();

$response = $import->DatabaseImport('orm.sql');


$import->dump( 
    $response['message'] 
);

?>
