<?php
    
use Tamedevelopers\Database\DBSchemaExport;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


// 'woocommerce'
$import = new DBSchemaExport(
    path: base_path('tests/database/orm.sql'),
    type: 'laravel'
);

// do this 
$response = $import->run();

dump( 
    $response
);

?>
