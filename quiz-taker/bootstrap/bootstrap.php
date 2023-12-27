<?php

declare(strict_types=1);

use eftec\bladeone\BladeOne;

require_once __DIR__ . '/../vendor/autoload.php';

// Show and log errors
ini_set('display_errors', '0');
ini_set('log_errors', '0');
error_reporting(-1); // E_ALL || -1

define('ROOT_DIR', dirname(__DIR__));

// Setup template engine
$templatesDirectory = ROOT_DIR . '/views/';
$cacheDirectory = ROOT_DIR . '/storage/cache/';

// From source code:
// MODE_AUTO - BladeOne reads if the compiled file has changed. If it has changed,then the file is replaced.
// MODE_SLOW - Then compiled file is always replaced. It's slow and it's useful for development.
// MODE_FAST - The compiled file is never replaced. It's fast and it's useful for production.
// MODE_DEBUG - DEBUG MODE, the file is always compiled and the filename is identifiable.
$blade = new BladeOne($templatesDirectory, $cacheDirectory, BladeOne::MODE_AUTO);
