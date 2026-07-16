<?php

namespace App\Services;
use Exception;
use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\ZatcaAPI;

class CertificateService
{
    /**
     * Current company data.
     */
    protected array $company = [];

    /**
     * Certificate settings.
     */
    protected array $settings = [];

    /**
     * Production credentials.
     */
    protected array $productionCredentials = [];

    /**
     * Compliance credentials.
     */
    protected array $complianceCredentials = [];

    /**
     * Company storage path.
     */
    protected string $companyPath = '';

    public function __construct()
    {
    }

    /**
     * Load current company and all required files.
     *
     * @throws Exception
     */
    private function loadCurrentCompany(): void
    {
        $currentCompanyFile = getCurrentCompanyStorageFile();

        if (!file_exists($currentCompanyFile)) {
            throw new Exception('Current company file not found.');
        }

        $currentCompany = json_decode(file_get_contents($currentCompanyFile), true);

        if (
            !is_array($currentCompany)
            || empty($currentCompany['crn'])
        ) {
            throw new Exception('Invalid current company.');
        }

        $crn = $currentCompany['crn'];

        $this->companyPath = getCompanyPath($crn);

        $this->company = loadJsonFile(
            getCompanyFile($crn, 'company.json')
        );

        $settingsFile = getCompanyFile($crn, 'certificate_settings.json');

        $this->settings = file_exists($settingsFile)
            ? loadJsonFile($settingsFile)
            : [];

        $credentialsFile = getCompanyFile(
            $crn,
            'production_credentials.json'
        );

        $this->productionCredentials = file_exists($credentialsFile)
            ? loadJsonFile($credentialsFile)
            : [];

        $complianceFile = getCompanyFile(
            $crn,
            'ZATCA_certificate_data.json'
        );
        
        $this->complianceCredentials = file_exists($complianceFile)
            ? loadJsonFile($complianceFile)
            : [];

    }

    /**
     * Backup file if it exists.
     *
     * @throws Exception
     */
    private function backupFile(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $backupDir = $this->companyPath . DIRECTORY_SEPARATOR . 'backup';

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
            throw new Exception("Unable to backup file: $file");
        }
    }

    /**
     * Backup company certificate files.
     * @throws Exception
     */
    private function backupCertificateFiles(
        bool $backupCsr = true,
        bool $backupPrivateKey = true,
        bool $backupProductionCredentials = false,
        bool $backupComplianceCredentials = false
    ): void {

        if ($backupCsr) {
            $this->backupFile(
                getCompanyFile($this->company['crn'], 'certificate.csr')
            );
        }

        if ($backupPrivateKey) {
            $this->backupFile(
                getCompanyFile($this->company['crn'], 'private.pem')
            );
        }

        if ($backupProductionCredentials) {
            $this->backupFile(
                getCompanyFile($this->company['crn'], 'production_credentials.json')
            );
        }

        if ($backupComplianceCredentials) {
            $this->backupFile(
                getCompanyFile($this->company['crn'], 'ZATCA_certificate_data.json')
            );
        }
    }

    private function getCsrPath(): string
    {
        return getCompanyFile($this->company['crn'], 'certificate.csr');
    }

    private function getProductionCredentialsPath(): string
    {
        return getCompanyFile($this->company['crn'], 'production_credentials.json');
    }

    /**
     * Generate CSR and Private Key.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function generateCSR(array $data): array
    {
        initializeCompany([
            'crn'          => $data['crn'] ?? '',
            'vat'          => $data['organization_identifier'] ?? '',
            'company_name' => $data['organization_name'] ?? '',
            'branch_name'  => $data['organizational_unit_name'] ?? '',
            'environment'  => $data['environment'] ?? 'nonprod'
        ]);

        // إعادة تحميل بيانات الشركة بعد initializeCompany
        $this->loadCurrentCompany();

        $environment = $data['environment'] ?? 'nonprod';

        $uuid = generateUUID();

        $commonName = getCommonNameByEnvironment($environment);

        $csrPath = getCSRFile();
        $keyPath = getPrivateKeyFile();
        $settingPath = getCertificateSettingsFile();

        // أخذ نسخة احتياطية إن كانت الملفات موجودة
        $this->backupCertificateFiles();

        $this->buildCertificate(
            $data,
            $uuid,
            $commonName,
            $csrPath,
            $keyPath
        );

        if (!file_exists($csrPath)) {
            throw new Exception('CSR file was not created.');
        }

        if (!file_exists($keyPath)) {
            throw new Exception('Private key file was not created.');
        }

        $settings = [
            'environment'        => $environment,
            'vat_number'         => $data['organization_identifier'],
            'crn'                => $data['crn'],
            'organization_name'  => $data['organization_name'],
            'branch_name'        => $data['organizational_unit_name'],
            'address'            => $data['address'],
            'street'             => $data['street'],
            'building_number'    => $data['building_number'],
            'subdivision'        => $data['subdivision'],
            'city'               => $data['city'],
            'postal_zone'        => $data['postal_zone'],
            'business_category'  => $data['business_category'],
            'invoice_type'       => $data['invoice_type'],
            'common_name'        => $commonName,
            'serial_1'           => $data['serial_1'],
            'serial_2'           => $data['serial_2'],
            'generated_uuid'     => $uuid,
            'generated_at'       => date('Y-m-d H:i:s')
        ];

        saveJsonFile($settingPath, $settings);

        updateCompanyStatus('csr_generated');

        $this->settings = $settings;

        return [
            'success' => true,
            'message' => 'Certificate generated successfully.'
        ];
    }

    /**
     * Build CSR using Saleh7 CertificateBuilder.
     *
     * @throws Exception
     */
    private function buildCertificate(
        array $data,
        string $uuid,
        string $commonName,
        string $csrPath,
        string $keyPath
    ): void {

        (new CertificateBuilder())
            ->setOrganizationIdentifier($data['organization_identifier'])
            ->setSerialNumber(
                $data['serial_1'],
                $data['serial_2'],
                $uuid
            )
            ->setCommonName($commonName)
            ->setCountryName($data['country_name'])
            ->setOrganizationName($data['organization_name'])
            ->setOrganizationalUnitName($data['organizational_unit_name'])
            ->setAddress($data['address'])
            ->setInvoiceType($data['invoice_type'])
            ->setEnvironment($data['environment'])
            ->setBusinessCategory($data['business_category'])
            ->generateAndSave($csrPath, $keyPath);
    }

    /**
     * Request compliance certificate from ZATCA.
     *
     * @param string $otp
     * @return array
     * @throws Exception
     */
    public function requestComplianceCertificate(string $otp): array
    {
        if (trim($otp) === '') {
            throw new Exception('OTP is required.');
        }

        $this->loadCurrentCompany();

        $environment = getApiEnvironment();

        $csrPath = getOutputFile('certificate.csr');

        if (!file_exists($csrPath)) {
            throw new Exception(
                'certificate.csr not found. Please generate the certificate first.'
            );
        }

        $zatcaClient = new ZatcaAPI($environment);

        $csr = $zatcaClient->loadCSRFromFile($csrPath);

        $result = $zatcaClient->requestComplianceCertificate(
            $csr,
            trim($otp)
        );

        $outputFile = getComplianceCertificateFile();

        $zatcaClient->saveToJson(
            $result->getCertificate(),
            $result->getSecret(),
            $result->getRequestId(),
            $outputFile
        );

        updateCompanyStatus(COMPANY_STATUS_COMPLIANCE);

        updateCurrentCompany([
            'last_request_id' => $result->getRequestId()
        ]);

        return [
            'success' => true,
            'message' => 'Compliance certificate requested successfully.',
            'data' => [
                'request_id' => $result->getRequestId(),
                'output_file' => basename($outputFile)
            ]
        ];
    }

    /**
     * Compliance check
     * @throws Exception
     */
    public function runComplianceCheck(): array
    {
        $settings    = loadCertificateSettings();

        $credentials = loadComplianceCredentials();
    
        $environment = getApiEnvironment();
    
        $supplier = buildSupplier($settings);
    
        $privateKey = loadPrivateKey();
    
        $outputDirectory = getComplianceOutputDirectory();
    
        $api = new ZatcaAPI($environment);
    
        $testInvoices = getComplianceInvoices($supplier);
    
        $results = [];
    
        $allPassed = true;
    
        $icv = 0;
    
        foreach ($testInvoices as $test) {
    
            $icv++;
    
            $invoice = $test['data'];
    
            $invoice['additionalDocuments'][0]['uuid'] = (string) $icv;
    
            $result = processComplianceInvoice(
    
                $api,
    
                $invoice,
    
                $credentials,
    
                $privateKey,
    
                $outputDirectory,
                
                $icv
    
            );
    
            $results[] = $result;
    
            if (!$result['success']) {
                $allPassed = false;
            }
        }
    
        if (!$allPassed) {
    
            jsonResponse(
                false,
                'Some invoices failed compliance.',
                $results
            );
        }
    
        $production = requestProductionCertificate(
    
            $api,
    
            $credentials,
    
            getProductionCredentialsFile()
    
        );

        return [
            'success' => true,
            'message' => 'Compliance completed successfully.',
            'data' => [
                'production' => $production,
                'invoices' => $results
            ]
        ];
    
    }

    /**
     * Generate Renewal CSR.
     *
     * @return array
     * @throws Exception
     */
    public function generateRenewCSR(): array
    {
        $this->loadCurrentCompany();

        if (empty($this->productionCredentials['certificate'])) {
            throw new Exception(
                'Production certificate not found.'
            );
        }

        $this->backupCertificateFiles();

        $uuid = generateUUID();

        $commonName = getCommonNameByEnvironment(
            $this->settings['environment']
        );

        $csrPath = getCSRFile();

        $keyPath = getPrivateKeyFile();

        (new CertificateBuilder())
            ->setOrganizationIdentifier($this->settings['vat_number'])
            ->setSerialNumber(
                $this->settings['serial_1'],
                $this->settings['serial_2'],
                $uuid
            )
            ->setCommonName($commonName)
            ->setCountryName('SA')
            ->setOrganizationName(
                $this->settings['organization_name']
            )
            ->setOrganizationalUnitName(
                $this->settings['branch_name']
            )
            ->setAddress(
                $this->settings['address']
            )
            ->setInvoiceType(
                $this->settings['invoice_type']
            )
            ->setEnvironment(
                $this->settings['environment']
            )
            ->setBusinessCategory(
                $this->settings['business_category']
            )
            ->generateAndSave(
                $csrPath,
                $keyPath
            );

        $this->settings['generated_uuid'] = $uuid;
        $this->settings['generated_at'] = date('Y-m-d H:i:s');

        saveJsonFile(
            getCertificateSettingsFile(),
            $this->settings
        );

        return [
            'success' => true,
            'message' => 'Renewal CSR generated successfully.'
        ];
    }

    /**
     * Renew Production Certificate.
     *
     * @param string $otp
     * @return array
     * @throws Exception
     */
    public function renewProductionCertificate(string $otp): array
    {
        $this->loadCurrentCompany();

        if (trim($otp) === '') {
            throw new Exception('OTP is required.');
        }

        if (empty($this->productionCredentials['certificate'])) {
            throw new Exception('Production certificate not found.');
        }

        if (empty($this->productionCredentials['secret'])) {
            throw new Exception('Production secret not found.');
        }

        $csrPath = $this->getCsrPath();

        if (!file_exists($csrPath)) {
            throw new Exception('Renewal CSR not found.');
        }

        $csr = file_get_contents($csrPath);

        if ($csr === false) {
            throw new Exception('Unable to read Renewal CSR.');
        }

        $api = new ZatcaAPI(getApiEnvironment());

        $result = $api->renewProductionCertificate(
            $this->productionCredentials['certificate'],
            $this->productionCredentials['secret'],
            $csr,
            trim($otp)
        );

        $this->backupCertificateFiles(
            false,
            false,
            true);

        $api->saveToJson(
            $result->getCertificate(),
            $result->getSecret(),
            $result->getRequestId(),
            $this->getProductionCredentialsPath()
        );

        updateCurrentCompany([
            'last_request_id' => $result->getRequestId()
        ]);

        return [
            'success' => true,
            'message' => 'Production certificate renewed successfully.',
            'data' => [
                'request_id' => $result->getRequestId()
            ]
        ];
    }    
}
