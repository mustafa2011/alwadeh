<?php

namespace App\Services;

use App\Core\Database;
use App\Models\User;

class AuthService
{
    private User $users;

    public function __construct()
    {
        $this->users = new User(Database::getConnection());

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);

        if (!$user) {
            return false;
        }

        if ((int)$user['is_active'] !== 1) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'user_role' => $user['user_role'],
        ];

        $this->users->updateLastLogin((int)$user['id']);

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        session_start();
        
        session_regenerate_id(true);        
    }

    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function check(): bool
    {
        return isset($_SESSION['user']);
    }
}