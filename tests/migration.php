<?php
    
use Tamedevelopers\Database\DBImport;
use Tamedevelopers\Database\Capsule\AppManager;
use Tamedevelopers\Database\Migrations\Migration;

include_once __DIR__ . "/../vendor/autoload.php";


$migration = new Migration();



dump( 
    $migration::create('users')
);

?>
