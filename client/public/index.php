<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/boostrap.php';

// load configs
$configs = require(ROOT_DIR . '/config/config.php');
echo $twig->render('index.html', [
    'config' => $configs,
]);
