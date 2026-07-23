<?php

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\CompanyStorageRepository;
use \Saleh7\Zatca\CertificateBuilder;
use PDO;
use Exception;

class CompanySettingsRepository
{
    private PDO $db;
    protected CompanyStorageRepository $storage;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->storage = new CompanyStorageRepository();
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

    public function loadSettings(): array
    {
        $company = $this->storage->loadCurrentCompany();

        $stmt = $this->db->prepare("
            SELECT *
            FROM company_zatca_settings
            WHERE company_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $company['id']
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }  

    public function getEnvironment(): string
    {
        $settings = $this->loadSettings();
    
        if (empty($settings['environment'])) {
            throw new Exception(
                'Environment not found in certificate settings.'
            );
        }
    
        return $settings['environment'];
    }

    public function getApiEnvironment()
    {
        switch ($this->getEnvironment()) {
    
            case CertificateBuilder::ENV_NONPROD:
                return 'sandbox';
    
            case CertificateBuilder::ENV_SIMULATION:
                return 'simulation';
    
            case CertificateBuilder::ENV_PRODUCTION:
                return 'production';
    
            default:
                throw new Exception('Invalid environment.');
        }
    }  
    
    function getCommonNameByEnvironment()
    {
        switch ($this->getEnvironment()) {

            case CertificateBuilder::ENV_SIMULATION:
                return 'PREZATCA-Code-Signing';

            case CertificateBuilder::ENV_NONPROD:
                return 'TSTZATCA-Code-Signing';

            case CertificateBuilder::ENV_PRODUCTION:
                return 'ZATCA-Code-Signing';

            default:
                throw new Exception('Invalid environment.');
        }
    }    
}