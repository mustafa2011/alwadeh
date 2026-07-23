<?php

namespace App\Repositories;
use App\Core\Database;
use PDO;
use Exception;
use Throwable;

class CompanyStorageRepository
{
    public function __construct()
    {
    }
    
    public function loadCurrentCompany(): array
    {
        if (empty($_SESSION['company_crn'])) {
            throw new Exception('No current company selected.');
        }
    
        $crn = trim($_SESSION['company_crn']);
    
        $company = $this->getCompany($crn);
    
        if (!$company) {
            throw new Exception("Company not found: {$crn}");
        }
    
        $company['crn'] = $company['commercial_registration_number'];
        $company['vat'] = $company['vat_number'];
        
        if (!empty($company['tax_scheme']['company_id_value'])) {
            $company['vat'] = $company['tax_scheme']['company_id_value'];
        }
    
    
        $companyId = (int)$company['id'];
    
        $company['party'] = $this->getCompanyParty($companyId) ?? [];
        $company['address'] = $this->getCompanyAddress($companyId) ?? [];
        $company['tax_scheme'] = $this->getCompanyTaxScheme($companyId) ?? [];
        $company['legal_entity'] = $this->getCompanyLegalEntity($companyId) ?? [];
    
        return $company;
    }    
    
    public function getCurrentCompany(): ?string
    {
        if (!empty($_SESSION['company_crn'])) {
            return $_SESSION['company_crn'];
        }
    
        $userId = $_SESSION['user']['id'] ?? null;
    
        if (!$userId) {
            return null;
        }
    
        $pdo = Database::getConnection();
    
        $stmt = $pdo->prepare("
            SELECT c.commercial_registration_number
            FROM user_current_company ucc
            INNER JOIN companies c ON c.id = ucc.company_id
            WHERE ucc.user_id = ?
            LIMIT 1
        ");
    
        $stmt->execute([$userId]);
    
        $crn = $stmt->fetchColumn();
    
        if ($crn) {
            $_SESSION['company_crn'] = $crn;
            return $crn;
        }
    
        return null;
    }    

    private function storagePath(): string
    {
        if (!is_dir(STORAGE_PATH)) {
            mkdir(STORAGE_PATH, 0755, true);
        }
    
        return STORAGE_PATH;
    }

    public function companyPath(?string $crn = null): string
    {
        $crn = $this->getCurrentCompany();

        if ($crn === null) {
            throw new Exception('No current company selected.');
        }
        $path = COMPANY_PATH . DIRECTORY_SEPARATOR . $crn;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    
        return $path;
    }

    public function getInvoicesDirectory(): string
    {
        return COMPANY_PATH . DIRECTORY_SEPARATOR . 'invoices';
    }

    public function csrPath(string $crn): string
    {
        return COMPANY_PATH . DIRECTORY_SEPARATOR . $crn . DIRECTORY_SEPARATOR . 'certificate.csr';
    }

    public function privateKeyPath(string $crn): string
    {
        return COMPANY_PATH . DIRECTORY_SEPARATOR . $crn . DIRECTORY_SEPARATOR . 'private.pem';
    }

    public function complianceDirectory(): string
    {
        return getComplianceOutputDirectory();
    }

    public function loadCSR(string $crn): string
    {
        if (!($this->csrPath($crn))) {
            throw new Exception('CSR file was not created.');
        }
         
        return file_get_contents($this->csrPath($crn));
    }

    public function loadPK(string $crn)
    {
        if (!$this->privateKeyPath($crn)) {
            throw new Exception('Private key file was not created.');
        }

        return file_get_contents($this->privateKeyPath($crn));
    }

    private function getCompanyParty(int $companyId): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM company_party WHERE company_id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getCompanyAddress(int $companyId): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM company_address WHERE company_id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getCompanyTaxScheme(int $companyId): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM company_tax_scheme WHERE company_id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getCompanyLegalEntity(int $companyId): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM company_legal_entity WHERE company_id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateCurrentCompany(array $data): bool
    {
        $crn = $this->getCurrentCompany();
    
        if (!$crn) {
            throw new Exception('No company selected.');
        }
    
        return $this->updateCompany($crn, $data);
    }
    
    public function updateCompany(string $crn, array $data): bool
    {
        if (!$this->companyExists($crn)) {
            return false;
        }
    
        $company = $this->getCompany($crn);
    
        if (!$company) {
            return false;
        }
    
        $companyId = (int)$company['id'];
    
        $company['party'] = $this->getCompanyParty($companyId) ?? [];
        $company['address'] = $this->getCompanyAddress($companyId) ?? [];
        $company['tax_scheme'] = $this->getCompanyTaxScheme($companyId) ?? [];
        $company['legal_entity'] = $this->getCompanyLegalEntity($companyId) ?? [];
    
        $protectedFields = [
            'crn',
            'created_at',
            'status'
        ];
    
        foreach ($data as $key => $value) {
            if (in_array($key, $protectedFields, true)) {
                continue;
            }
            $company[$key] = $value;
        }
    
        return $this->saveCompany($crn, $company);
    }

    public function getAllCompanies(): array
    {
        $db = Database::getConnection();

        $sql = "
            SELECT
                c.id,
                c.commercial_registration_number AS crn,
                c.vat_number AS vat,
                c.environment,
                c.status AS is_active,
                c.company_name,
                ca.city_subdivision_name AS branch_name,
                c.created_at,
                c.updated_at
            FROM companies c
            LEFT JOIN company_address ca
                ON ca.company_id = c.id
            ORDER BY c.company_name ASC
        ";

        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function companyExists(string $crn): bool
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT id 
            FROM companies
            WHERE commercial_registration_number = ?
            LIMIT 1
        ");

        $stmt->execute([$crn]);

        return (bool)$stmt->fetchColumn();
    }

    public function createCompany(array $data): bool
    {
        $pdo = Database::getConnection();

        if (empty($data['crn'])) {
            return false;
        }

        $userId = $_SESSION['user']['id'] ?? 0;

        if ($userId <= 0) {
            throw new Exception('User is not authenticated.');
        }

        $pdo->beginTransaction();

        try {

            $stmt = $pdo->prepare("
                INSERT INTO companies
                (
                    user_id,
                    company_name,
                    registration_name,
                    commercial_registration_number,
                    vat_number,
                    company_type,
                    currency_code,
                    country_code,
                    environment
                )
                VALUES
                (?,?,?,?,?,?,?,?,?)
            ");

            $stmt->execute([
                $userId,
                $data['company_name'] ?? '',
                $data['company_name'] ?? '',
                $data['crn'],
                $data['vat'] ?? '',
                'seller',
                'SAR',
                'SA',
                $data['environment'] ?? 'nonprod'
            ]);

            $companyId = (int)$pdo->lastInsertId();

            try {
                $this->createCompanyParty($companyId, $data);
            } catch (Throwable $e) {
                throw new Exception("PARTY: ".$e->getMessage());
            }
            
            try {
                $this->createCompanyAddress($companyId, $data);
            } catch (Throwable $e) {
                throw new Exception("ADDRESS: ".$e->getMessage());
            }
            
            try {
                $this->createCompanyTaxScheme($companyId, $data);
            } catch (Throwable $e) {
                throw new Exception("TAX: ".$e->getMessage());
            }
            
            try {
                $this->createCompanyLegalEntity($companyId, $data);
            } catch (Throwable $e) {
                throw new Exception("LEGAL: ".$e->getMessage());
            }        

            $pdo->commit();

            return true;

        } catch (Throwable $e) {

            $pdo->rollBack();

            throw $e;
        }
    }

    private function createCompanyParty(int $companyId, array $data): void
    {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO company_party
            (
                company_id,
                party_identification_id,
                party_identification_scheme,
                name
            )
            VALUES (?,?,?,?)
        ");

        $stmt->execute([
            $companyId,
            $data['crn'],
            'CRN',
            $data['company_name']
        ]);
    }

    private function createCompanyAddress(int $companyId, array $data): void
    {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO company_address
            (
                company_id,
                street_name,
                building_number,
                city_subdivision_name,
                city_name,
                postal_zone,
                country_identification_code
            )
            VALUES (?,?,?,?,?,?,?)
        ");

        $stmt->execute([
            $companyId,
            $data['street'] ?? '',
            $data['building_number'] ?? '',
            $data['subdivision'] ?? '',
            $data['city'] ?? '',
            $data['postal_zone'] ?? '',
            'SA'
        ]);
    }

    private function createCompanyTaxScheme(int $companyId, array $data): void
    {

        $stmt = Database::getConnection()->prepare("
            INSERT INTO company_tax_scheme
            (
                company_id,
                tax_scheme_id,
                company_id_value
            )
            VALUES (?,?,?)
        ");

        $stmt->execute([
            $companyId,
            'VAT',
            $data['vat'] ?? ''
        ]);
    }

    private function createCompanyLegalEntity(int $companyId, array $data): void
    {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO company_legal_entity
            (
                company_id,
                registration_name,
                company_id_value,
                company_id_scheme
            )
            VALUES (?,?,?,?)
        ");

        $stmt->execute([
            $companyId,
            $data['company_name'] ?? '',
            $data['crn'] ?? '',
            'CRN'
        ]);
    }

    public function getCompany(string $crn): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM companies WHERE commercial_registration_number = ? LIMIT 1");
        $stmt->execute([$crn]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function saveCompany(string $crn, array $company): bool
    {

        $company['updated_at'] = date('c');

        // Sync company to database
        $this->syncCompanyToDatabase($crn, $company);

        return true;
    }

    public function deleteCompany(string $crn): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            DELETE FROM companies
            WHERE commercial_registration_number = ?
        ");
        $stmt->execute([$crn]);
        $path = getCompanyPath($crn);
        if (is_dir($path)) {
            $this->deleteDirectoryRecursive($path);
        }
        if ($crn === $this->getCurrentCompany()) {
            $this->clearCurrentCompany();
        }

        return $stmt->rowCount() > 0;
    }

    private function deleteDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectoryRecursive($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function setCurrentCompany(string $crn): bool
    {
        $pdo = Database::getConnection();

        $userId = $_SESSION['user']['id'] ?? 0;

        if (!$userId) {
            throw new Exception('User not authenticated.');
        }

        $stmt = $pdo->prepare("
            SELECT id
            FROM companies
            WHERE commercial_registration_number = ?
            LIMIT 1
        ");

        $stmt->execute([$crn]);

        $companyId = $stmt->fetchColumn();

        if (!$companyId) {
            throw new Exception('Company not found.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO user_current_company
            (user_id, company_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE
                company_id = VALUES(company_id),
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            $userId,
            $companyId
        ]);

        $_SESSION['company_crn'] = $crn;

        return true;
    }

    public function getCurrentCompanyInfo(): ?array
    {
        $crn = $this->getCurrentCompany();

        if (!$crn) {
            return null;
        }

        return $this->getCompany($crn);
    }

    private function clearCurrentCompany(): void
    {
        unset($_SESSION['company_crn']);
    }

    public function getCurrentCompanyPath(): ?string
    {
        $crn = $this->getCurrentCompany();

        if (!$crn) {
            return null;
        }

        return getCompanyPath($crn);
    }

    public function companyFile(string $file): string
    {
        $crn = $this->getCurrentCompany();

        if (!$crn) {
            throw new Exception('No company selected.');
        }

        return STORAGE_PATH . DIRECTORY_SEPARATOR .  $file;
    }

    public function compliancePath(): string
    {
        $path = COMPANY_PATH . DIRECTORY_SEPARATOR . 'compliance';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    public function invoicesPath(): string
    {
        $path = COMPANY_PATH . DIRECTORY_SEPARATOR . 'invoices';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    public function logsPath(): string
    {
        return COMPANY_PATH . DIRECTORY_SEPARATOR . 'logs';
    }

    public function backupPath(): string
    {
        $path = COMPANY_PATH . DIRECTORY_SEPARATOR . 'backup';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    public function initializeCompany(array $data): array
    {
        if (empty($data['crn'])) {
            throw new Exception('CRN is required.');
        }
    
        $crn = trim($data['crn']);
    
        if (!$this->companyExists($crn)) {
            $this->createCompany($data);
        }
    
        $this->setCurrentCompany($crn);
    
        return $this->loadCurrentCompany();
    }

    private function syncCompanyToDatabase(string $crn, array $company): int
    {
        $pdo = Database::getConnection();

        $userId = $_SESSION['user']['id'] ?? 0;

        if ($userId <= 0) {
            throw new Exception('User is not authenticated.');
        }

        $stmt = $pdo->prepare("
            SELECT id
            FROM companies
            WHERE commercial_registration_number = ?
            LIMIT 1
        ");
        $stmt->execute([$crn]);
        $companyId = $stmt->fetchColumn();

        if (!$companyId) {

            $stmt = $pdo->prepare("
                INSERT INTO companies
                (
                    user_id,
                    company_name,
                    registration_name,
                    commercial_registration_number,
                    vat_number,
                    company_type,
                    currency_code,
                    country_code,
                    status
                )
                VALUES (?,?,?,?,?,?,?,?,1)
            ");

            $stmt->execute([
                $userId,
                $company['company_name'] ?? '',
                $company['company_name'] ?? '',
                $crn,
                $company['vat'] ?? null,
                'seller',
                'SAR',
                'SA'
            ]);

            $companyId = (int)$pdo->lastInsertId();

        } else {

            $stmt = $pdo->prepare("
                UPDATE companies
                SET
                    company_name = ?,
                    registration_name = ?,
                    vat_number = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $company['company_name'] ?? '',
                $company['company_name'] ?? '',
                $company['vat'] ?? null,
                $companyId
            ]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO company_party
            (
                company_id,
                party_identification_id,
                party_identification_scheme,
                name
            )
            VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name)
        ");

        $stmt->execute([
            $companyId,
            $crn,
            'CRN',
            $company['company_name'] ?? ''
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO company_address
            (
                company_id,
                street_name,
                building_number,
                city_subdivision_name,
                city_name,
                postal_zone,
                country_identification_code
            )
            VALUES (?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
                street_name = VALUES(street_name),
                building_number = VALUES(building_number),
                city_subdivision_name = VALUES(city_subdivision_name),
                city_name = VALUES(city_name),
                postal_zone = VALUES(postal_zone),
                country_identification_code = VALUES(country_identification_code)
            ");

        $address = $company['address'] ?? [];
        $stmt->execute([
            $companyId,
            $address['street_name'] ?? '',
            $address['building_number'] ?? '',
            $address['city_subdivision_name'] ?? '',
            $address['city_name'] ?? '',
            $address['postal_zone'] ?? '',
            'SA'
        ]);       

        $stmt = $pdo->prepare("
            INSERT INTO company_tax_scheme
            (
                company_id,
                tax_scheme_id,
                company_id_value
            )
            VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE
                company_id_value = VALUES(company_id_value)
        ");

        $stmt->execute([
            $companyId,
            'VAT',
            $company['tax_scheme']['company_id_value']
                ?? $company['vat_number']
                ?? $company['vat']
                ?? ''
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO company_legal_entity
            (
                company_id,
                registration_name,
                company_id_value,
                company_id_scheme
            )
            VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE
                registration_name = VALUES(registration_name),
                company_id_value = VALUES(company_id_value)
        ");

        $stmt->execute([
            $companyId,
            $company['company_name'] ?? '',
            $crn,
            'CRN'
        ]);

        return (int)$companyId;
    }

    public function updateCompanyStatus(string $status, bool $value = false): bool
    {
        $crn = $this->getCurrentCompany();

        if (!$crn) {
            return false;
        }

        $pdo = Database::getConnection();

        $column = match ($status) {
            'csr_generated' => COMPANY_STATUS_CSR,
            'compliance_certificate' => COMPANY_STATUS_COMPLIANCE,
            'production_certificate' => COMPANY_STATUS_PRODUCTION ,
            default => null
        };

        if (!$column) {
            return false;
        }

        $stmt = $pdo->prepare("
            UPDATE companies
            SET {$column} = ?
            WHERE commercial_registration_number = ?
        ");

        return $stmt->execute([
            $value ? 1 : 0,
            $crn
        ]);
    }

    public function getCompanyZatcaSettings(): array
    {
        if (empty($_SESSION['company_crn'])) {
            throw new Exception('No current company selected.');
        }
    
        $crn = trim($_SESSION['company_crn']);
        $company = $this->getCompany($crn);

        $companyId = $company['id'];

        $stmt = Database::getConnection()->prepare("
            SELECT *
            FROM company_zatca_settings
            WHERE company_id = ?
            LIMIT 1
        ");
    
        $stmt->execute([$companyId]);
    
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$settings) {
            throw new Exception('Certificate settings not found.');
        }
    
        return $settings;
    }
    
    public function syncCertificateCompanyData(int $companyId, array $data): void
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO company_address
            (
                company_id,
                street_name,
                building_number,
                city_subdivision_name,
                city_name,
                postal_zone,
                country_identification_code
            )
            VALUES (?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
                street_name = VALUES(street_name),
                building_number = VALUES(building_number),
                city_subdivision_name = VALUES(city_subdivision_name),
                city_name = VALUES(city_name),
                postal_zone = VALUES(postal_zone)
        ");

        $stmt->execute([
            $companyId,
            $data['street'] ?? '',
            $data['building_number'] ?? '',
            $data['subdivision'] ?? '',
            $data['city'] ?? '',
            $data['postal_zone'] ?? '',
            'SA'
        ]);

        $stmt = $pdo->prepare("
            UPDATE company_party
            SET name = ?
            WHERE company_id = ?
        ");

        $stmt->execute([
            $data['organization_name'] ?? '',
            $companyId
        ]);

        $stmt = $pdo->prepare("
            UPDATE company_legal_entity
            SET
                registration_name = ?,
                company_id_value = ?,
                company_id_scheme = ?,
                registration_address = ?
            WHERE company_id = ?
        ");

        $stmt->execute([
            $data['organization_name'] ?? '',
            $data['organization_identifier'] ?? '',
            'CRN',
            ($data['building_number'] ?? '') . ', ' .
            ($data['street'] ?? '') . ', ' .
            ($data['city'] ?? '') . ', ' .
            ($data['postal_zone'] ?? '') . ', SA',
            $companyId
        ]);

        $stmt = $pdo->prepare("
            UPDATE company_tax_scheme
            SET company_id_value = ?
            WHERE company_id = ?
        ");

        $stmt->execute([
            $data['organization_identifier'] ?? '',
            $companyId
        ]);
    }

    public function backupFile(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
    
        $backupDir = dirname($file) . DIRECTORY_SEPARATOR . 'backup';
    
        if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true)) {
            throw new Exception('Unable to create backup directory.');
        }
    
        $info = pathinfo($file);
    
        $backupFile = $backupDir
            . DIRECTORY_SEPARATOR
            . $info['filename']
            . '_'
            . date('Ymd_His');
    
        if (!empty($info['extension'])) {
            $backupFile .= '.' . $info['extension'];
        }
    
        if (!copy($file, $backupFile)) {
            throw new Exception("Unable to backup file: {$file}");
        }
    }
    
    public function backupCertificateFiles(
        bool $backupCsr = true,
        bool $backupPrivateKey = true
    ): void {
        $csrPath = $this->csrPath($this->getCurrentCompany());   
        $keyPath = $this->privateKeyPath($this->getCurrentCompany());        
        if ($backupCsr) {
            $this->backupFile($csrPath);
        }
    
        if ($backupPrivateKey) {
            $this->backupFile($keyPath);
        }
    }

    public function buildCertificate(
        array $data,
        string $uuid,
        string $commonName,
    ): void {
    
        (new \App\Builders\CertificateBuilderFactory())
            ->create(
                $this->loadCurrentCompany(),
                $data,
                $uuid,
                $commonName
            )
            ->generateAndSave(
                $this->csrPath($this->getCurrentCompany()),
                $this->privateKeyPath($this->getCurrentCompany())
            );
    }    

}