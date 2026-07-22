<?php

 use App\Core\Database;
 use Saleh7\Zatca\Api\ProductionCertificateResult;

function saveProductionCredentials(
    ProductionCertificateResult $result
): void
{
    $pdo = Database::getConnection();

    $company = (new App\Repositories\CompanyStorageRepository())->loadCurrentCompany();

    $stmt = $pdo->prepare("
        UPDATE company_zatca_settings
        SET
            production_certificate_content = ?,
            production_secret = ?,
            production_pcsid = ?,
            request_id = ?,
            status = 'approved',
            environment = ?,
            updated_at = NOW()
        WHERE company_id = ?
    ");

    $stmt->execute([
        $result->getCertificate(),
        $result->getSecret(),
        $result->getCertificate(),
        $result->getRequestId(),
        getDatabaseEnvironment(),
        $company['id']
    ]);
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
            serial_number,
            certificate_name,            
            csr_content,
            private_key_content,
            status,
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
            :serial_number,
            :certificate_name,
            :csr_content,
            :private_key_content,
            :status,            
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
            serial_number      = VALUES(serial_number),
            certificate_name   = VALUES(certificate_name),
            csr_content         = VALUES(csr_content),
            private_key_content = VALUES(private_key_content),
            status = IF(status IS NULL OR status = 'generated', VALUES(status), status),
            generated_at       = VALUES(generated_at)
    ");

    $stmt->execute([
        'company_id'          => $settings['company_id'],
        'environment'         => $settings['environment'],
        'certificate_path'    => $settings['certificate_path'],
        'private_key_path'    => $settings['private_key_path'],
        'vat_number'          => $settings['vat_number'],
        'crn'                 => $settings['crn'],
        'organization_name'   => $settings['organization_name'],
        'branch_name'         => $settings['branch_name'],
        'address'             => $settings['address'],
        'street'              => $settings['street'],
        'building_number'     => $settings['building_number'],
        'subdivision'         => $settings['subdivision'],
        'city'                => $settings['city'],
        'postal_zone'         => $settings['postal_zone'],
        'business_category'   => $settings['business_category'],
        'invoice_type'        => $settings['invoice_type'],
        'common_name'         => $settings['common_name'],
        'serial_1'            => $settings['serial_1'],
        'serial_2'            => $settings['serial_2'],
        'generated_uuid'      => $settings['generated_uuid'],
        'serial_number'       => $settings['serial_number'],
        'certificate_name'    => $settings['certificate_name'],
        'csr_content'         => $settings['csr_content'],
        'private_key_content' => $settings['private_key_content'],
        'status'              => $settings['status'],
        'generated_at'        => $settings['generated_at'],
    ]);
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
            compliance_csid = ?,
            compliance_certificate_content = ?,
            compliance_secret = ?,
            status = 'submitted',
            updated_at = NOW()
        WHERE company_id = ?
    ");

    $stmt->execute([
        $requestId,
        $requestId,
        $certificate,
        $certificate,
        $secret,
        $companyId
    ]);
}

function saveComplianceCertificate(
    object $result,
    string $csr,
    string $privateKey
): void
{
    $company = (new App\Repositories\CompanyStorageRepository())->loadCurrentCompany();

    if (empty($company['id'])) {
        throw new Exception('Company not found.');
    }

    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("
        UPDATE company_zatca_settings
        SET
            compliance_certificate_content = ?,
            csr_content = ?,
            private_key_content = ?,
            compliance_secret = ?,
            compliance_request_id = ?,
            request_id = ?,
            status = 'submitted',
            updated_at = NOW()
        WHERE company_id = ?
    ");

    $stmt->execute([
        $result->getCertificate(),
        $csr,
        $privateKey,
        $result->getSecret(),
        $result->getRequestId(),
        $result->getRequestId(),
        $company['id']
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

function updateCertificateValidity(int $companyId, string $certificate): void
{
    $x509 = new \phpseclib3\File\X509();

    $x509->loadX509($certificate);

    $cert = $x509->getCurrentCert();

    $validFrom = $cert['tbsCertificate']['validity']['notBefore']['utcTime']
        ?? null;

    $validTo = $cert['tbsCertificate']['validity']['notAfter']['utcTime']
        ?? null;

    $serial = $cert['tbsCertificate']['serialNumber']->toString()
        ?? null;

    $stmt = Database::getConnection()->prepare("
        UPDATE company_zatca_settings
        SET
            valid_from = ?,
            valid_to = ?,
            expires_at = ?,
            serial_number = ?,
            updated_at = NOW()
        WHERE company_id = ?
    ");

    $notBefore = $cert['tbsCertificate']['validity']['notBefore']['utcTime'] ?? null;
    $notAfter  = $cert['tbsCertificate']['validity']['notAfter']['utcTime'] ?? null;
    
    $validFrom = $notBefore ? new DateTimeImmutable($notBefore) : null;
    $validTo   = $notAfter ? new DateTimeImmutable($notAfter) : null;

    if (!$validFrom || !$validTo) {
        throw new Exception('Invalid certificate validity.');
    }    

    $stmt->execute([
        $validFrom->format('Y-m-d'),
        $validTo->format('Y-m-d'),
        $validTo->format('Y-m-d H:i:s'),
        $serial,
        $companyId
    ]);

}


