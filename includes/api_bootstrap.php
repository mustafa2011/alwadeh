<?php

/**
 * API Bootstrap (Final Stable Version)
 * -------------------------------
 * Central initialization for all ZATCA APIs
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Riyadh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Disable HTTP Cache for APIs
|--------------------------------------------------------------------------
*/

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

define('APP_ROOT', dirname(__DIR__));
define('API_PATH', __DIR__);
define('HELPERS_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'helpers');
define('STORAGE_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'Storage');
define('COMPANY_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'Companies');

$autoload = APP_ROOT . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    throw new RuntimeException("Composer autoload.php not found: {$autoload}");
}

require_once $autoload;
require_once HELPERS_PATH . '/auth_helper.php';
require_once APP_ROOT . '/includes/config.php';
use App\Middleware\AuthMiddleware;

require_once HELPERS_PATH . '/common_helper.php';

require_once HELPERS_PATH . '/file_helper.php';
require_once HELPERS_PATH . '/storage_helper.php';

require_once HELPERS_PATH . '/company_status.php';
require_once HELPERS_PATH . '/invoice_helper.php';

$currentScript = basename($_SERVER['SCRIPT_NAME']);

$publicApis = [
    'login.php',
];

if (!in_array($currentScript, $publicApis, true)) {
    AuthMiddleware::handle();
}

function jsonResponse(
    bool $success,
    string $message,
    array $data = [],
    int $code = 200
): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function requirePostRequest()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Invalid request method.', [], 405);
    }
}
