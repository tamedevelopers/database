<?php
    
use Tamedevelopers\Database\DBExport;

include_once __DIR__ . "/../vendor/autoload.php";


// 'woocommerce'
// $export = new DBExport('zip', 'woocommerce');
$export = new DBExport('zip');

dd(
    $export->run(),
    'ss',
);