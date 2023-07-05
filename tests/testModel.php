<?php

use Post;
    
include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/Model/Post.php";


// using model
Post::where('id', 2)->get();

// 
db()->table('posts')->get();

?>
