<?php

use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/Model/Post.php";
include_once __DIR__ . "/Model/Admin.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


$db = db('woocommerce');
$admin = new Admin;

// $collection = tcollect($db->table('users')->get());

// foreach($collection as $user ){
//     // $user
// }
// using model
// $admin->getUsers()
// 

dd(
    $admin->getUsers(),
    DB::from("admins")->paginate(1),
    // Admin::where('id', 2)->first(),
    // DB::select('SHOW TABLES'),
    // DB::selectOne("SHOW CREATE TABLE `admins`"),
    $db->from('t'),
    // DB::from("admins"),
);

// using model
Post::where('id', 2)->get();

// 
db()->table('posts')->get();

?>
