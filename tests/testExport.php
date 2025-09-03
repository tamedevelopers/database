<?php
    
use Tamedevelopers\Database\DBExport;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


// 'woocommerce'
$export = new DBExport('zip', 'woocommerce');

dd(
    // $export->run(),
    'ss',
);