<?php

use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/Model/Post.php";
include_once __DIR__ . "/Model/Admin.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
AppManager::bootLoader();


$db = db();
$admin = new Admin;

dd(
    $db,
    $admin->getUsers()
);

// using model
Post::where('id', 2)->get();

// 
db()->table('posts')->get();

?>
