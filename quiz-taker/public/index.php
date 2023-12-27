<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/bootstrap.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


$configs = [
    'master_key' => $_ENV['MASTER_KEY'],
    'WS_URL' => $_ENV['WS_URL'],
    'COOKIE_DOMAIN' => $_ENV['COOKIE_DOMAIN'],
];

echo $blade->run('index', [
    'config' => $configs,
]);
