<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Show and log errors
ini_set('display_errors', "0");
ini_set('log_errors', "0");
error_reporting(-1); // E_ALL || -1

define('ROOT_DIR', dirname(__DIR__));

// Setup template engine
$loader = new \Twig\Loader\FilesystemLoader(ROOT_DIR . '/views/');
$twig = new \Twig\Environment($loader, array(
//    'cache' => false,
    'cache' => ROOT_DIR . '/storage/cache',
    'debug' => false, // true,
));
