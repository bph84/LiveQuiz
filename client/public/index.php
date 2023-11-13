<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/bootstrap.php';

$configs = require(ROOT_DIR . '/config/config.php');
echo $blade->run('index', [
    'config' => $configs,
]);
