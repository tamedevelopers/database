<?php
    
use UltimateOrmDatabase\DBImport;

include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/init-configuration.php";


$import = new DBImport();

$response = $import->DatabaseImport('orm.sql');


$import->dump( 
    $response['message'] 
);

?>
