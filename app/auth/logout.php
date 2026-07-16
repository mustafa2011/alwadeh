<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;

$auth = new AuthService();

$auth->logout();

header('Location: ' . BASE_URL . '/pages/login.php');
exit;