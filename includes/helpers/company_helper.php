<?php
/**
 * Company Helper
 *
 * Handles company management and current company selection.
 */

require_once __DIR__ . '/storage_helper.php';
require_once __DIR__ . '/common_helper.php';
require_once __DIR__ . '/file_helper.php';

use App\Core\Database;

/**
 * Get all registered companies.
 *
 * @return array
 */

function getAllCompanies(): array
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

/**
 * Returns company.json path.
 */
function getCompanyJsonFile(string $crn): string
{
    return getCompanyFile($crn, 'company.json');
}
/**
 * Check if company exists.
 */
function companyExists(string $crn): bool
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

/**
 * Create company.
 */
function createCompany(array $data): bool
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
            createCompanyParty($companyId, $data);
        } catch (Throwable $e) {
            throw new Exception("PARTY: ".$e->getMessage());
        }
        
        try {
            createCompanyAddress($companyId, $data);
        } catch (Throwable $e) {
            throw new Exception("ADDRESS: ".$e->getMessage());
        }
        
        try {
            createCompanyTaxScheme($companyId, $data);
        } catch (Throwable $e) {
            throw new Exception("TAX: ".$e->getMessage());
        }
        
        try {
            createCompanyLegalEntity($companyId, $data);
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
function createCompanyParty(int $companyId, array $data): void
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
function createCompanyAddress(int $companyId, array $data): void
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
function createCompanyTaxScheme(int $companyId, array $data): void
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
function createCompanyLegalEntity(int $companyId, array $data): void
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

/**
 * Load company.
 */

function getCompany(string $crn): ?array
{
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM companies WHERE commercial_registration_number = ? LIMIT 1");
    $stmt->execute([$crn]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Save company.
 */
function saveCompany(string $crn, array $company): bool
{

    $company['updated_at'] = date('c');

    // Sync company to database
    syncCompanyToDatabase($crn, $company);

    return true;
}

/**
 * Delete company.
 */
function deleteCompany(string $crn): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        DELETE FROM companies
        WHERE commercial_registration_number = ?
    ");
    $stmt->execute([$crn]);
    $path = getCompanyPath($crn);
    if (is_dir($path)) {
        deleteDirectoryRecursive($path);
    }
    if ($crn === getCurrentCompany()) {
        clearCurrentCompany();
    }

    return $stmt->rowCount() > 0;
}

/**
 * Recursive folder delete.
 */
function deleteDirectoryRecursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = array_diff(scandir($dir), ['.', '..']);

    foreach ($items as $item) {

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            deleteDirectoryRecursive($path);
        } else {
            unlink($path);
        }
    }

    rmdir($dir);
}

/**
 * Set current company.
 *
 * @param string $crn
 *  
 */

 function setCurrentCompany(string $crn): void
{
    $_SESSION['company_crn'] = $crn;
}


/**
 * Get current company CRN.
 */
// function getCurrentCompany(): ?string
// {
//     $file = getCurrentCompanyStorageFile();

//     if (!file_exists($file)) {
//         return null;
//     }

//     $data = loadJsonFile($file);

//     if (empty($data['crn'])) {
//         return null;
//     }

//     $_SESSION['current_company'] = $data['crn'];

//     return $data['crn'];
// }
function getCurrentCompany(): ?string
{
    return $_SESSION['company_crn'] ?? null;
}
/**
 * Get current company information.
 */
function getCurrentCompanyInfo(): ?array
{
    $crn = getCurrentCompany();

    if (!$crn) {
        return null;
    }

    return getCompany($crn);
}

/**
 * Clear current company.
 */
function clearCurrentCompany(): void
{
    unset($_SESSION['company_crn']);
}

/**
 * Get current company path.
 */
function getCurrentCompanyPath(): ?string
{
    $crn = getCurrentCompany();

    if (!$crn) {
        return null;
    }

    return getCompanyPath($crn);
}

/**
 * Returns current company directory.
 */
function companyPath(): string
{
    $crn = getCurrentCompany();

    if (!$crn) {
        throw new Exception('No company selected.');
    }

    return getCompanyPath($crn);
}

/**
 * Returns file path inside current company.
 */
function companyFile(string $file): string
{
    $crn = getCurrentCompany();

    if (!$crn) {
        throw new Exception('No company selected.');
    }

    return getCompanyFile($crn, $file);
}

/**
 * Returns compliance directory.
 */
function compliancePath(): string
{
    return companyPath() . '/compliance';
}

/**
 * Returns invoice directory.
 */
function invoicesPath(): string
{
    return companyPath() . '/invoices';
}

/**
 * Returns logs directory.
 */
function logsPath(): string
{
    return companyPath() . '/logs';
}

/**
 * Returns backup directory.
 */
function backupPath(): string
{
    return companyPath() . '/backup';
}

/**
 * Initialize current company.
 */
function initializeCompany(array $data): array
{
    if (empty($data['crn'])) {
        throw new Exception('CRN is required.');
    }

    $crn = trim($data['crn']);

    if (!companyExists($crn)) {
        createCompany($data);
    }

    setCurrentCompany($crn);

    $company = getCompany($crn);

    if (!$company) {
        throw new Exception("Company not found: {$crn}");
    }
    return $company;
}

/**
 * Update company information.
 *
 * @param string $crn
 * @param array  $data
 * @return bool
 */
function updateCompany(string $crn, array $data): bool
{
    if (!companyExists($crn)) {
        return false;
    }

    $company = getCompany($crn);

    if (!$company) {
        return false;
    }

    $companyId = (int)$company['id'];

    $company['party'] = getCompanyParty($companyId) ?? [];
    $company['address'] = getCompanyAddress($companyId) ?? [];
    $company['tax_scheme'] = getCompanyTaxScheme($companyId) ?? [];
    $company['legal_entity'] = getCompanyLegalEntity($companyId) ?? [];

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

    return saveCompany($crn, $company);
}

/**
 * Update current company.
 *
 * @param array  $data
 * @return bool
 */
function updateCurrentCompany(array $data): bool
{
    $crn = getCurrentCompany();

    if (!$crn) {
        throw new Exception('No company selected.');
    }

    return updateCompany($crn, $data);
}

/**
 * Update company onboarding status.
 */
function updateCompanyStatus(string $status, bool $value = true): bool
{
    $crn = getCurrentCompany();

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



/**
 * Validate company data.
 *
 * @param array $data
 *
 * @throws Exception
 */
function validateCompanyData(array $data): void
{
    if (empty($data['company_name'])) {
        throw new Exception('Company name is required.');
    }

    if (empty($data['crn'])) {
        throw new Exception('CRN is required.');
    }

    if (!preg_match('/^1\d{9}$/', $data['crn'])) {
        throw new Exception(
            'CRN must be exactly 10 digits and start with 1.'
        );
    }

    if (empty($data['vat'])) {
        throw new Exception('VAT number is required.');
    }

    if (!preg_match('/^3\d{13}3$/', $data['vat'])) {
        throw new Exception(
            'VAT must be exactly 15 digits and start/end with 3.'
        );
    }

    if (empty($data['environment'])) {
        throw new Exception('Environment is required.');
    }
}

function syncCompanyToDatabase(string $crn, array $company): int
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

function syncCertificateCompanyData(int $companyId, array $data): void
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

function getCompanyParty(int $companyId): ?array
{
    $stmt = Database::getConnection()->prepare("SELECT * FROM company_party WHERE company_id = ? LIMIT 1");
    $stmt->execute([$companyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getCompanyAddress(int $companyId): ?array
{
    $stmt = Database::getConnection()->prepare("SELECT * FROM company_address WHERE company_id = ? LIMIT 1");
    $stmt->execute([$companyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getCompanyTaxScheme(int $companyId): ?array
{
    $stmt = Database::getConnection()->prepare("SELECT * FROM company_tax_scheme WHERE company_id = ? LIMIT 1");
    $stmt->execute([$companyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getCompanyLegalEntity(int $companyId): ?array
{
    $stmt = Database::getConnection()->prepare("SELECT * FROM company_legal_entity WHERE company_id = ? LIMIT 1");
    $stmt->execute([$companyId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Load current company and all required files.
 *
 * @throws Exception
 */  
function loadCurrentCompany(): array
{
    if (empty($_SESSION['company_crn'])) {
        throw new Exception('No current company selected.');
    }

    $crn = trim($_SESSION['company_crn']);

    $company = getCompany($crn);

    if (!$company) {
        throw new Exception("Company not found: {$crn}");
    }

    $company['crn'] = $company['commercial_registration_number'];
    $company['vat'] = $company['vat_number'];
    
    if (!empty($company['tax_scheme']['company_id_value'])) {
        $company['vat'] = $company['tax_scheme']['company_id_value'];
    }


    $companyId = (int)$company['id'];

    $company['party'] = getCompanyParty($companyId) ?? [];
    $company['address'] = getCompanyAddress($companyId) ?? [];
    $company['tax_scheme'] = getCompanyTaxScheme($companyId) ?? [];
    $company['legal_entity'] = getCompanyLegalEntity($companyId) ?? [];

    return $company;
}