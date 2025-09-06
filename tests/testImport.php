<?php
    
use Tamedevelopers\Database\DBImport;

include_once __DIR__ . "/../vendor/autoload.php";


$import = new DBImport(null, base_path('tests/database/orm.sql'));

// do this 
$response = $import->run();

// of function helper
// 'woocommerce' is the database connection name you want to use
// $same = import('woocommerce', 'orm.sql',);
// $same->run()


dump( 
    'sss',
    $response
);

?>
