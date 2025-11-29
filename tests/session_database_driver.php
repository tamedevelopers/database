<?php

use Tamedevelopers\Database\Session\SessionManager;

require __DIR__ . '/../vendor/autoload.php';


config(['session.driver' => 'database']);


$session = new SessionManager([
    'lifetime' => 1800,
    'connection' => 'sqlite', //sqlite
]);

$session->start();
$session->put('db_key', 'db_value');
// $session->forget('db_key');
// $session->destroy('db_key');

dd(
    $_SERVER['HTTP_USER_AGENT']
);

dd(
    $_SESSION,
    $session->config(),
    $session->id(),
    $session->get('db_key'),

    db('sqlite')->table('admins')
);

echo 'Session ID: ' . $session->id() . PHP_EOL;
echo 'db_key=' . $session->get('db_key') . PHP_EOL;