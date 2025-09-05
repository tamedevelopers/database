<?php
    
use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\DBExport;
use Tamedevelopers\Database\Capsule\AppManager;
use Tamedevelopers\Support\Collections\Collection;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


// 'woocommerce'
$export = new DBExport('zip', 'woocommerce');


$users = DB::table("user")->paginate();


// $users->showing();
$query = DB::query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

dd(
    DB::query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
        ->get()
        ->pluck('tables_in_test'),

    DB::query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")
        ->get()
        ->pluck('tables_in_test'),

    'ss',
);