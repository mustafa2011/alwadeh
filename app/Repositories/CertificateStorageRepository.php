<?php

namespace App\Repositories;
use App\Core\Database;
use PDO;

class CertificateStorageRepository
{
    protected CompanyStorageRepository $storage;

    public function __construct()
    {
        $this->storage = new CompanyStorageRepository();
    }

    public function loadSettings(): array
    {
        $company = (new \App\Repositories\CompanyStorageRepository())->loadCurrentCompany();
    
        $pdo = Database::getConnection();
    
        $stmt = $pdo->prepare("
            SELECT *
            FROM company_zatca_settings
            WHERE company_id = ?
            LIMIT 1
        ");
    
        $stmt->execute([
            $company['id']
        ]);
    
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $settings ?: [];
    }

    public function loadProductionCredentials(): array
    {
        $settings = $this->loadSettings();
    
        if (empty($settings)) {
            return [];
        }
    
        return [
            'certificate' => $settings['production_certificate_content'] ?? '',
            'secret'      => $settings['production_secret'] ?? '',
            'request_id'  => $settings['request_id'] ?? '',
        ];
    }
    
    public function loadComplianceCredentials(): array
    {
        $settings = $this->loadSettings();
    
        if (empty($settings)) {
            return [];
        }
    
        return [
            'certificate' => $settings['compliance_certificate_content'] ?? '',
            'secret'      => $settings['compliance_secret'] ?? '',
            'request_id'  => $settings['compliance_request_id'] ?? '',
        ];
    }
    
    public function loadPrivateKey(): string
    {
        $settings = $this->loadSettings();
    
        return $settings['private_key_content'] ?? '';
    }

}