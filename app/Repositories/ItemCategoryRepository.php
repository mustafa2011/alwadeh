<?php

namespace App\Repositories;
use App\Core\Database;
use PDO;

class ItemCategoryRepository
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
                category_code,
                category_name,
                description
            FROM item_categories
            WHERE company_id = ?
            ORDER BY category_name'
        );

        $stmt->execute([$companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $companyId, int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                category_code,
                category_name,
                description
            FROM item_categories
            WHERE company_id = ?
            AND id = ?
            LIMIT 1'
        );

        $stmt->execute([$companyId, $id]);

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        return $category ?: null;
    }
}