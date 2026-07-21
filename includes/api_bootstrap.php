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

/*
|--------------------------------------------------------------------------
| 1. Define Core Paths
|--------------------------------------------------------------------------
*/

define('APP_ROOT', dirname(__DIR__));


define('API_PATH', __DIR__);
define('HELPERS_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'helpers');
define('STORAGE_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'Storage');
define('COMPANY_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'Companies');

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
require_once HELPERS_PATH . '/auth_helper.php';
require_once APP_ROOT . '/includes/config.php';
use App\Middleware\AuthMiddleware;

/*
|--------------------------------------------------------------------------
| 3. Load Helpers (STRICT ORDER - IMPORTANT)
|--------------------------------------------------------------------------
*/

// 1. Config / Core
require_once HELPERS_PATH . '/config_helper.php';
require_once HELPERS_PATH . '/common_helper.php';

// 2. File & Storage Layer
require_once HELPERS_PATH . '/file_helper.php';
require_once HELPERS_PATH . '/storage_helper.php';

// 3. Business Layer
// require_once HELPERS_PATH . '/company_helper.php';
require_once HELPERS_PATH . '/company_status.php';
require_once HELPERS_PATH . '/certificate_helper.php';
require_once HELPERS_PATH . '/invoice_helper.php';

/*
|--------------------------------------------------------------------------
| 4. Authentication
|--------------------------------------------------------------------------
*/

$currentScript = basename($_SERVER['SCRIPT_NAME']);

$publicApis = [
    'login.php',
];

if (!in_array($currentScript, $publicApis, true)) {
    AuthMiddleware::handle();
}

/*
|--------------------------------------------------------------------------
| 5. JSON Response Safety
|--------------------------------------------------------------------------
*/

// if (!function_exists('jsonResponse')) {
//     function jsonResponse(array $data, int $code = 200): void
//     {
//         http_response_code($code);
//         header('Content-Type: application/json; charset=UTF-8');
//         // echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
//         jsonResponse([
//             'success' => true,
//             'data' => $data
//         ]);
//         exit;
//     }
// }
if (!function_exists('jsonResponse')) {
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
}