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
        $stmt = $this->db->query("
            SELECT *
            FROM companies
            ORDER BY id ASC
            LIMIT 1
        ");
    
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $company ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM companies
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        return $company ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM companies
            ORDER BY company_name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);

        $sql = sprintf(
            "INSERT INTO companies (%s) VALUES (%s)",
            implode(',', $columns),
            implode(',', array_fill(0, count($columns), '?'))
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        unset($data['id']);

        $fields = [];

        foreach (array_keys($data) as $column) {
            $fields[] = "{$column} = ?";
        }

        $sql = "
            UPDATE companies
            SET " . implode(', ', $fields) . "
            WHERE id = ?
        ";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM companies
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public function setActive(int $id): bool
    {
        $this->db->exec("UPDATE companies SET is_active = 0");

        $stmt = $this->db->prepare("
            UPDATE companies
            SET is_active = 1
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }

    public function exists(): bool
    {
        return $this->getActive() !== null;
    }    
}