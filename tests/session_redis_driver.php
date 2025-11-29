<?php

use Tamedevelopers\Support\Process\SessionManager;

require __DIR__ . '/../vendor/autoload.php';

use Predis\Client;
use Tamedevelopers\Support\Process\Http;

$redis = new Client([
    'scheme' => 'tcp',
    'host'   => Http::host(),
    'port'   => 6379,
    'database' => 1,
    'prefix' => 'tame:',
]);

$redis->set('test', 'Hello Redis!');

dd(
    $redis->ping(),
    $redis->get('test'),
    Http::host()
);

$session = new SessionManager([
    'driver' => 'redis',
    'lifetime' => 1800,
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 1,
        'prefix' => 'tame:',
    ],
]);

$session->start();
$session->put('redis_key', 'redis_value');

echo 'Session ID: ' . $session->id() . PHP_EOL;
echo 'redis_key=' . $session->get('redis_key') . PHP_EOL;