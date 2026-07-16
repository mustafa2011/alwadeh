<?php

/**
 * Page Bootstrap
 * -------------------------------
 * Central initialization for all Portal pages
 */

declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Riyadh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| 1. Define Core Paths
|--------------------------------------------------------------------------
*/

define('APP_ROOT', dirname(__DIR__));

define('PAGES_PATH', APP_ROOT . '/pages');
define('HELPERS_PATH', APP_ROOT . '/includes/helpers');
define('STORAGE_PATH', APP_ROOT . '/Storage');

/*
|--------------------------------------------------------------------------
| 2. Composer Autoload
|--------------------------------------------------------------------------
*/

$autoload = APP_ROOT . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    throw new RuntimeException("Composer autoload.php not found: {$autoload}");
}

require_once $autoload;
require_once APP_ROOT . '/includes/config.php';
/*
|--------------------------------------------------------------------------
| 3. Load Helpers
|--------------------------------------------------------------------------
*/

require_once HELPERS_PATH . '/auth_helper.php';

/*
|--------------------------------------------------------------------------
| 4. Authentication
|--------------------------------------------------------------------------
*/

$currentScript = basename($_SERVER['SCRIPT_NAME']);

$publicPages = [
    'login.php',
];

if (!in_array($currentScript, $publicPages, true)) {
    \App\Middleware\AuthMiddleware::handle(false);
}