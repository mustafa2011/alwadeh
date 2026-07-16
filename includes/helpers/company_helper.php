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
    $companies = [];

    $companiesPath = getCompaniesPath();

    if (!is_dir($companiesPath)) {
        return [];
    }

    $folders = scandir($companiesPath);

    foreach ($folders as $folder) {

        if ($folder === '.' || $folder === '..') {
            continue;
        }

        $companyPath = getCompanyPath($folder);

        if (!is_dir($companyPath)) {
            continue;
        }

        $companyFile = getCompanyJsonFile($folder);

        if (!file_exists($companyFile)) {
            continue;
        }

        $company = loadJsonFile($companyFile);

        if (!is_array($company)) {
            continue;
        }

        $companies[] = $company;
    }

    usort($companies, function ($a, $b) {
        return strcmp(
            $a['company_name'] ?? '',
            $b['company_name'] ?? ''
        );
    });

    return $companies;
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
    return file_exists(getCompanyJsonFile($crn));
}

/**
 * Create company.
 */
function createCompany(array $data): bool
{
    if (empty($data['crn'])) {
        return false;
    }

    $crn = trim($data['crn']);

    ensureCompanyDirectories($crn);

    $company = [

        'crn'               => $crn,
        'vat'               => $data['vat'] ?? '',
        'company_name'      => $data['company_name'] ?? '',
        'branch_name'       => $data['branch_name'] ?? '',
        'environment'       => $data['environment'] ?? 'noprod',

        'status' => [
            'csr_generated'          => false,
            'compliance_certificate' => false,
            'production_certificate' => false,
        ],

        'created_at' => date('c'),
        'updated_at' => date('c'),
    ];

    saveCompany($crn, $company);

    return true;
}

/**
 * Load company.
 */
function getCompany(string $crn): ?array
{
    if (!companyExists($crn)) {
        return null;
    }

    return loadJsonFile(getCompanyJsonFile($crn));
}

/**
 * Save company.
 */
function saveCompany(string $crn, array $company): bool
{
    $company['updated_at'] = date('c');

    saveJsonFile(
        getCompanyJsonFile($crn),
        $company
    );

    syncCompanyToDatabase($crn, $company);

    return true;
}

/**
 * Delete company.
 */
function deleteCompany(string $crn): bool
{
    $path = getCompanyPath($crn);

    if (!is_dir($path)) {
        return false;
    }

    deleteDirectoryRecursive($path);

    if ($crn === getCurrentCompany()) {
        clearCurrentCompany();
    }

    return true;
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
 * @return bool
 */
function setCurrentCompany(string $crn): bool
{
    if (!companyExists($crn)) {
        return false;
    }

    $_SESSION['current_company'] = $crn;

    saveJsonFile(
        getCurrentCompanyStorageFile(),
        [
            'crn'        => $crn,
            'updated_at' => date('c')
        ]
    );

    return true;
}

/**
 * Get current company CRN.
 */
function getCurrentCompany(): ?string
{
    $file = getCurrentCompanyStorageFile();

    if (!file_exists($file)) {
        return null;
    }

    $data = loadJsonFile($file);

    if (empty($data['crn'])) {
        return null;
    }

    $_SESSION['current_company'] = $data['crn'];

    return $data['crn'];
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
    unset($_SESSION['current_company']);

    $file = getCurrentCompanyStorageFile();

    if (file_exists($file)) {
        unlink($file);
    }
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
        throw new Exception('Unable to load company.');
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

    $company = getCompany($crn);

    if (!$company) {
        return false;
    }

    if (!isset($company['status'])) {
        $company['status'] = [];
    }

    $company['status'][$status] = $value;
    $company['updated_at'] = date('c');

    saveJsonFile(getCompanyJsonFile($crn), $company);

    return true;
}

/**
 * Returns current company storage file.
 */
function getCurrentCompanyFile(): string
{
    return getCurrentCompanyStorageFile();
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

    if ($companyId) {

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

        return (int)$companyId;
    }

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
        VALUES
        (
            ?,?,?,?,?,?,?,?,1
        )
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

    return (int)$pdo->lastInsertId();
}