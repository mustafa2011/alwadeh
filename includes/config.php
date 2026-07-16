<?php

// Project Name
define('APP_NAME', 'ALWADEH ZATCA Portal');

// Base HOST
define('HOST_URL', 'http://localhost');

// Base URL
define('BASE_URL', HOST_URL . '/alwadeh');

// Timezone
date_default_timezone_set('Asia/Riyadh');

// Error Reporting
$isProduction = getenv('APP_ENV') === 'production';

if ($isProduction) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}