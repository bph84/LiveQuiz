<?php

declare(strict_types=1);


require_once __DIR__ . '/../bootstrap/boostrap.php';

require(ROOT_DIR . '/config.php');
echo $twig->render('index.html', [
    'config' => $configs,
]);