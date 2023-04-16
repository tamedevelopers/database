<?php

use UltimateOrmDatabase\DB;
use UltimateOrmDatabase\AutoloadEnv;

include_once __DIR__ . "/vendor/autoload.php";


// start environment configuration
AutoloadEnv::start([
    'path'  => '',
    'bg'    => 'green',
]);

$db = new DB();
$user = $db->table('tb_user')
            ->getArr();

$db->dump(
    $user,
    // ORM_ENV_CLASS,
    'ss'
);