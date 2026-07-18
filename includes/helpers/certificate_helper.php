<?php
/**
 * Certificate Helper Functions
 *
 * Handles certificate generation and certificate-related data.
 *
 * Responsibilities:
 * - Supplier information
 * - Certificate settings
 * - Private key loading
 * - Compliance credentials
 * - Production credentials
 * 
 */

 use App\Core\Database;

if (!function_exists('buildSupplier')) {

    /**
     * Build supplier array from certificate settings.
     *
     * @param array $company
     * @return array
     */

    function buildSupplier($company): array
    {
        return [
            'registrationName'   => $company['legal_entity']['registration_name'] ?? '',
            'taxId'              => $company['tax_scheme']['company_id_value'] ?? '',
            'identificationId'   => $company['commercial_registration_number'] ?? '',
            'identificationType' => 'CRN',
    
            'address' => [
                'street'         => $company['address']['street_name'] ?? '',
                'buildingNumber' => $company['address']['building_number'] ?? '',
                'subdivision'    => $company['address']['city_subdivision_name'] ?? '',
                'city'           => $company['address']['city_name'] ?? '',
                'postalZone'     => $company['address']['postal_zone'] ?? '',
                'country'        => $company['address']['country_identification_code'] ?? 'SA',
            ],    
            'taxScheme' => [
                'id' => $company['tax_scheme']['tax_scheme_id'] ?? 'VAT',
            ],
        ];
    }  
}

if (!function_exists('loadComplianceCredentials')) {

    /**
     * Load compliance certificate credentials.
     *
     * @return array
     * @throws Exception
     */
    function loadComplianceCredentials(): array
    {
        $company = loadCurrentCompany();
    
        $pdo = Database::getConnection();
    
        $stmt = $pdo->prepare("
            SELECT *
            FROM company_zatca_credentials
            WHERE company_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
    
        $stmt->execute([
            $company['id']
        ]);
    
        $credentials = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$credentials) {
            throw new Exception('Compliance credentials not found.');
        }
    
        return $credentials;
    }
}

if (!function_exists('loadPrivateKey')) {

    /**
     * Load and clean private key.
     *
     * @return string
     * @throws Exception
     */
    function loadPrivateKey()
    {
        $company = loadCurrentCompany();
        $pdo = Database::getConnection();
    
        $stmt = $pdo->prepare("
            SELECT private_key_content
            FROM company_zatca_settings
            WHERE company_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
    
        $stmt->execute([
            $company['id']
        ]);
    
        $privateKey = $stmt->fetchColumn();
    
        if (!$privateKey) {
            throw new Exception('Private key not found.');
        }
    
        return trim(
            preg_replace(
                '/-----(?:BEGIN|END)(?: EC)? PRIVATE KEY-----/',
                '',
                $privateKey
            )
        );
    }
}

if (!function_exists('saveProductionCredentials')) {

    /**
     * Save production certificate credentials.
     *
     * @param string $certificate
     * @param string $secret
     * @param string $requestId
     * @return void
     * @throws Exception
     */
    function saveProductionCredentials(
        string $certificate,
        string $secret,
        string $requestId
    ): void {
        $company = loadCurrentCompany();
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                SELECT id
                FROM company_zatca_credentials
                WHERE company_id = ?
                AND environment = ?
                LIMIT 1
            ");
            $stmt->execute([
                $company['id'],
                getApiEnvironment()
            ]);
            if ($stmt->fetchColumn()) {
                $pdo->commit();
                return;
            }
            $stmt = $pdo->prepare("
                INSERT INTO company_zatca_settings
                (
                    company_id,
                    certificate_type,
                    certificate_content,
                    environment,
                    status
                )
                VALUES (?,?,?,?,?)
            ");
            $stmt->execute([
                $company['id'],
                'production',
                $certificate,
                getApiEnvironment(),
                'approved'
            ]);
            $certificateId = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare("
                INSERT INTO company_zatca_credentials
                (
                    company_id,
                    certificate_id,
                    request_id,
                    binary_security_token,
                    secret,
                    environment
                )
                VALUES (?,?,?,?,?,?)
            ");
            $stmt->execute([
                $company['id'],
                $certificateId,
                $requestId,
                $certificate,
                $secret,
                getApiEnvironment()
            ]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}

/**
 * Save compliance certificate.
 *
 * @param object $result
 * @param string $csr
 * @param string $privateKey
 * @return int
 * @throws Exception
 */
function saveComplianceCertificate(
    object $result,
    string $csr,
    string $privateKey
): int {
    $company = loadCurrentCompany();
    $pdo = Database::getConnection();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            SELECT id
            FROM company_zatca_settings
            WHERE company_id = ?
            LIMIT 1
        ");
        $stmt->execute([$company['id']]);
        $existingCertificate = $stmt->fetchColumn();
        if ($existingCertificate) {
            $pdo->commit();
            return (int)$existingCertificate;
        }
        $stmt = $pdo->prepare("
            INSERT INTO company_zatca_settings
            (
                company_id,
                certificate_type,
                private_key_content,
                csr_content,
                certificate_serial,
                environment,
                status
            )
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $company['id'],
            'compliance',
            $privateKey,
            $csr,
            null,
            getApiEnvironment(),
            'approved'
        ]);
        $certificateId = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare("
            INSERT INTO company_zatca_credentials
            (
                company_id,
                certificate_id,
                request_id,
                binary_security_token,
                secret,
                environment
            )
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $company['id'],
            $certificateId,
            $result->getRequestId(),
            $result->getCertificate(),
            $result->getSecret(),
            getApiEnvironment()
        ]);
        $pdo->commit();
        return $certificateId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function saveCertificateSettings(array $settings): void
{
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("
        INSERT INTO company_zatca_settings (
            company_id,
            environment,
            certificate_path,
            private_key_path,
            vat_number,
            crn,
            organization_name,
            branch_name,
            address,
            street,
            building_number,
            subdivision,
            city,
            postal_zone,
            business_category,
            invoice_type,
            common_name,
            serial_1,
            serial_2,
            generated_uuid,
            generated_at
        ) VALUES (
            :company_id,
            :environment,
            :certificate_path,
            :private_key_path,
            :vat_number,
            :crn,
            :organization_name,
            :branch_name,
            :address,
            :street,
            :building_number,
            :subdivision,
            :city,
            :postal_zone,
            :business_category,
            :invoice_type,
            :common_name,
            :serial_1,
            :serial_2,
            :generated_uuid,
            :generated_at
        )
        ON DUPLICATE KEY UPDATE
            environment        = VALUES(environment),
            certificate_path   = VALUES(certificate_path),
            private_key_path   = VALUES(private_key_path),
            vat_number         = VALUES(vat_number),
            crn                = VALUES(crn),
            organization_name  = VALUES(organization_name),
            branch_name        = VALUES(branch_name),
            address            = VALUES(address),
            street             = VALUES(street),
            building_number    = VALUES(building_number),
            subdivision        = VALUES(subdivision),
            city               = VALUES(city),
            postal_zone        = VALUES(postal_zone),
            business_category  = VALUES(business_category),
            invoice_type       = VALUES(invoice_type),
            common_name        = VALUES(common_name),
            serial_1           = VALUES(serial_1),
            serial_2           = VALUES(serial_2),
            generated_uuid     = VALUES(generated_uuid),
            generated_at       = VALUES(generated_at)
    ");

    $stmt->execute($settings);
}

function updateComplianceSettings(
    int $companyId,
    string $requestId,
    string $certificate,
    string $secret
): void
{
    $stmt = Database::getConnection()->prepare("
        UPDATE company_zatca_settings
        SET
            compliance_request_id = ?,
            request_id = ?,
            compliance_certificate_content = ?,
            compliance_secret = ?,
            updated_at = NOW()
        WHERE company_id = ?
    ");

    $stmt->execute([
        $requestId,
        $requestId,
        $certificate,
        $secret,
        $companyId
    ]);
}

function getCertificateSettings(int $companyId): array
{
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
