<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Company
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getActive(): ?array
    {
        $sql = "
            SELECT *
            FROM companies
            WHERE is_active = 1
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);

        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        return $company ?: null;
    }
}