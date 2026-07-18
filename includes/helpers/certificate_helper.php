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
        $privateKey = file_get_contents(
            getCompliancePrivateKeyPath()
        );

        if ($privateKey === false) {
            throw new Exception('Unable to read private key.');
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
                INSERT INTO company_zatca_certificates
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

if (!function_exists('saveComplianceCertificate')) {

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
                FROM company_zatca_certificates
                WHERE company_id = ?
                AND certificate_type = 'compliance'
                LIMIT 1
            ");
            $stmt->execute([$company['id']]);
            $existingCertificate = $stmt->fetchColumn();
            if ($existingCertificate) {
                $pdo->commit();
                return (int)$existingCertificate;
            }
            $stmt = $pdo->prepare("
                INSERT INTO company_zatca_certificates
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
}

