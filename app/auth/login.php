<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\AuthService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);

    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {

    echo json_encode([
        'success' => false,
        'message' => 'Email and Password are required.'
    ]);

    exit;
}

$auth = new AuthService();

$user = $auth->login($email, $password);

if (!$user) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid Credentials'
    ]);

    exit;
}

session_start();

$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'full_name' => $user['full_name'],
    'user_role' => $user['user_role']
];

echo json_encode([
    'success' => true,
    'message' => 'Login Success'
]);