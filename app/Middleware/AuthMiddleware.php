<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware
{
    public static function authenticate(): void
    {
        $auth = new AuthService();

        if (!$auth->check()) {

            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' => 'Authentication required.'
            ]);

            exit;
        }
    }

    public static function authorize(array $roles): void
    {
        self::authenticate();

        $user = $_SESSION['user'];

        if (!in_array($user['role'], $roles, true)) {

            http_response_code(403);

            echo json_encode([
                'success' => false,
                'message' => 'Permission denied.'
            ]);

            exit;
        }
    }

    public static function handle(bool $apiRequest = true): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            return;
        }

        if ($apiRequest) {

            http_response_code(401);

            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]);

            exit;
        }

        header("Location: " . BASE_URL . "/pages/login.php");
        exit;
    }

}