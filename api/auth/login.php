<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| Accept POST Requests Only
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Read Request
|--------------------------------------------------------------------------
*/

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

try {

    $auth = new AuthService();

    if (!$auth->login($email, $password)) {
    
        http_response_code(401);
    
        echo json_encode([
            'success'=>false,
            'message'=>'Invalid email or password.'
        ]);
    
        exit;
    }
    
    echo json_encode([
        'success'=>true
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);

    exit;
}