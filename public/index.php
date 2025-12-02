<?php


// Before start check exsitance of vendor
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('vendor not found. Please run "composer install" to install dependencies.');
}

$app = require_once dirname(__DIR__, 1) . '/' . 'server.php';

$kernel = new \Oxygen\Core\Kernel($app);

$request = \Oxygen\Core\Request::capture();
$kernel->handle($request);