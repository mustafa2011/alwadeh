<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ItemUnitRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(int $companyId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                unit_code,
                unit_name,
                ubl_unit_code
            FROM item_units
            WHERE company_id = ?
            ORDER BY unit_name'
        );

        $stmt->execute([$companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $companyId, int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                unit_code,
                unit_name,
                ubl_unit_code
            FROM item_units
            WHERE company_id = ?
            AND id = ?
            LIMIT 1'
        );

        $stmt->execute([$companyId, $id]);

        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        return $unit ?: null;
    }
}