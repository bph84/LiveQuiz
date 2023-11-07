<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';


// Setup template engine
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../views/');
$twig = new \Twig\Environment($loader, array(
    //    'cache' => __DIR__ . '/../storage/cache',
    'cache' => false,
    'debug' => false, // true,
));

echo $twig->render('index.html', []);
