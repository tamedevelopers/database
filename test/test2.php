<?php
    
include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/others/PostClass.php";



$Posts = new PostClass();
$Posts->getUsers();

?>
