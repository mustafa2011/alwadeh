<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
    
        $stmt->execute([$email]);
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $user ?: null;
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET last_login = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$id]);
    }
}