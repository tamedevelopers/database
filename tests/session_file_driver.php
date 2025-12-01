<?php

use Tamedevelopers\Database\Session;
use Tamedevelopers\Database\Session\SessionManager;


require __DIR__ . '/../vendor/autoload.php';

// File session driver with custom directory
$session = new SessionManager([
    'lifetime' => 1800,
]);

$session->start();
$session->put('foo', 'bar');

echo 'foo=' . var_export($session->get('foo'), true) . PHP_EOL;

$session->forget('foo');

echo 'Session ID: ' . $session->id() . PHP_EOL;
echo 'foo=' . var_export($session->get('foo'), true) . PHP_EOL;