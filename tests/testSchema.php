<?php
    
use Tamedevelopers\Database\DBSchemaExport;

include_once __DIR__ . "/../vendor/autoload.php";

// 'woocommerce'
$import = new DBSchemaExport(
    path: base_path('tests/database/orm.sql'),
    type: 'default' // laravel|default
);

// do this 
$response = $import->run();

dump( 
    $response
);

?>
