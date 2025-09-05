<?php
    
use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\DBExport;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


// 'woocommerce'
$export = new DBExport('zip', 'woocommerce');


$users = DB::table("user")->paginate();

foreach ($users as $key => $value) {
    // dd(
    //     $value->user_id,
    //     $value->getAttributes(),
    //     $value->getOriginal(),
    //     $value->numbers(),
    //     // $value->select(['first_name']),
    // );
}

$users->showing();

$query = DB::query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

dd(
    $query->get()->pluck('tables_in_test')->all(),
    $query
    // $query->get()->map(fn($item) => $item['tables_in_test'])
        // ->pluck('tables_in_test')
        ,
    // $users->getPagination(),
    // $users,
    // $export->run(),
    // DB::query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->get()
        // ->pluck('tables_in_test')
        // ->select('tables_in_test')
    'ss',
);