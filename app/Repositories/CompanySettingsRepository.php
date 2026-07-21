<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CompanySettingsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function find(int $companyId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM company_zatca_settings
            WHERE company_id = ?
            LIMIT 1
        ");

        $stmt->execute([$companyId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function save(array $data): void
    {
        $exists = $this->find((int)$data['company_id']);

        if ($exists) {
            $this->update($data);
            return;
        }

        $columns = array_keys($data);

        $sql = sprintf(
            "INSERT INTO company_zatca_settings (%s) VALUES (%s)",
            implode(',', $columns),
            implode(',', array_fill(0, count($columns), '?'))
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
    }

    public function update(array $data): void
    {
        $companyId = (int)$data['company_id'];

        unset($data['company_id']);

        $fields = [];

        foreach (array_keys($data) as $column) {
            $fields[] = "{$column} = ?";
        }

        $sql = "
            UPDATE company_zatca_settings
            SET " . implode(',', $fields) . "
            WHERE company_id = ?
        ";

        $stmt = $this->db->prepare($sql);

        $values = array_values($data);
        $values[] = $companyId;

        $stmt->execute($values);
    }

    public function updateCompliance(
        int $companyId,
        string $requestId,
        string $certificate,
        string $secret
    ): void {

        $stmt = $this->db->prepare("
            UPDATE company_zatca_settings
            SET
                compliance_request_id = ?,
                compliance_certificate_content = ?,
                compliance_secret = ?,
                status = 'approved'
            WHERE company_id = ?
        ");

        $stmt->execute([
            $requestId,
            $certificate,
            $secret,
            $companyId
        ]);
    }

    public function updateProduction(
        int $companyId,
        string $requestId,
        string $certificate,
        string $secret
    ): void {

        $stmt = $this->db->prepare("
            UPDATE company_zatca_settings
            SET
                request_id = ?,
                production_certificate_content = ?,
                production_secret = ?
            WHERE company_id = ?
        ");

        $stmt->execute([
            $requestId,
            $certificate,
            $secret,
            $companyId
        ]);
    }

    public function updateValidity(
        int $companyId,
        string $validFrom,
        string $validTo,
        string $expiresAt
    ): void {

        $stmt = $this->db->prepare("
            UPDATE company_zatca_settings
            SET
                valid_from = ?,
                valid_to = ?,
                expires_at = ?
            WHERE company_id = ?
        ");

        $stmt->execute([
            $validFrom,
            $validTo,
            $expiresAt,
            $companyId
        ]);
    }
}