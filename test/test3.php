<?php
    
use UltimateOrmDatabase\DBImport;
use UltimateOrmDatabase\AutoloadEnv;

include_once __DIR__ . "/../vendor/autoload.php";

// start env configuration
AutoloadEnv::start();




$import = new DBImport();

$response = $import->DatabaseImport('orm.sql');


$import->dump( 
    $response['message'] 
);

?>
