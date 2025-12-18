<?php

use Illuminate\Foundation\Application;
// Debug for Product POST
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/products') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('/tmp/index_product_debug.log', "Index Reached: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
}

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());