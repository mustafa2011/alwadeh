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

    public function __construct() {}
    
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
    }

    private function getCsrPath(): string
    {
        return getCompanyFile($this->company['crn'], 'certificate.csr');
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
        $company = initializeCompany([
            'crn'          => $data['crn'] ?? '',
            'vat'          => $data['organization_identifier'] ?? '',
            'company_name' => $data['organization_name'] ?? '',
            'branch_name'  => $data['organizational_unit_name'] ?? '',
            'environment'  => $data['environment'] ?? 'nonprod'
        ]);
        
        setCurrentCompany($data['crn']);
        
        $this->company = loadCurrentCompany();        

        syncCertificateCompanyData(
            (int)$this->company['id'],
            $data
        );
        
        // To update company address
        $this->company = loadCurrentCompany();
        $company = $this->company;        

        $environment = $data['environment'] ?? 'nonprod';
    
        $uuid = generateUUID();
    
        $commonName = getCommonNameByEnvironment($environment);
    
        $csrPath = getCSRFile();
        $keyPath = getPrivateKeyFile();
        
        // Take backup if certificate files exist
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
            'company_id'         => (int) $company['id'],
            'environment'        => $environment,
        
            'vat_number'         => $company['tax_scheme']['company_id_value'] ?? '',
            'crn'                => $company['commercial_registration_number'] ?? '',
            'organization_name'  => $company['registration_name'] ?? '',
            'branch_name'        => $data['organizational_unit_name'],
            'address'            => $data['address'],
        
            'street'             => $company['address']['street_name'] ?? '',
            'building_number'    => $company['address']['building_number'] ?? '',
            'subdivision'        => $company['address']['city_subdivision_name'] ?? '',
            'city'               => $company['address']['city_name'] ?? '',
            'postal_zone'        => $company['address']['postal_zone'] ?? '',
        
            'business_category'  => $data['business_category'],
            'invoice_type'       => $data['invoice_type'],
        
            'common_name'        => $commonName,
            'serial_1'           => $data['serial_1'],
            'serial_2'           => $data['serial_2'],
            'generated_uuid'     => $uuid,
            'serial_number'      => $data['serial_1'].'|'.$data['serial_2'].'|'.$uuid,
            'certificate_name'   => $data['serial_1'].'_'.$data['serial_2'].'_'.$uuid,            
            'csr_content'        => file_get_contents($csrPath),
            'private_key_content' => file_get_contents($keyPath),
            'status'             => 'generated',            
            'generated_at'       => date('Y-m-d H:i:s'),
        
            'certificate_path'   => $csrPath,
            'private_key_path'   => $keyPath
        ];
    
         saveCertificateSettings($settings);    
        updateCompanyStatus(COMPANY_STATUS_CSR);
    
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
    
        $this->company = loadCurrentCompany();
   
        if (empty($this->company)) {
            throw new Exception('No current company selected.');
        }
    
        $csrPath = getCSRFile();
    
        if (!file_exists($csrPath)) {
            throw new Exception(
                'certificate.csr not found. Please generate the CSR first.'
            );
        }
    
        $environment = getApiEnvironment();
    
        $api = new ZatcaAPI($environment);
    
        $csr = $api->loadCSRFromFile($csrPath);
    
        $result = $api->requestComplianceCertificate(
            $csr,
            trim($otp)
        );
    
        updateComplianceSettings(
            (int)$this->company['id'],
            $result->getRequestId(),
            $result->getCertificate(),
            $result->getSecret()
        );


        
        $company = loadCurrentCompany();

        updateCertificateValidity(
            (int)$company['id'],
            $result->getCertificate()
        );
        
        $this->backupCertificateFiles(
            false,
            false,
            false,
            true
        );
        
        $privateKey = file_get_contents(getPrivateKeyFile());
        
        saveComplianceCertificate(
            $result,
            $csr,
            $privateKey
        );
    
        updateCompanyStatus(COMPANY_STATUS_COMPLIANCE);
    
        updateCurrentCompany([
            'last_request_id' => $result->getRequestId()
        ]);
    
        return [
            'success' => true,
            'message' => 'Compliance certificate requested successfully.',
            'data' => [
                'request_id' => $result->getRequestId()
            ]
        ];
    }

    /**
     * Compliance check
     * @throws Exception
     */
    public function runComplianceCheck(): array
    {
        $company = loadCurrentCompany();

        $credentials = loadComplianceCredentials();
        
        $environment = getApiEnvironment();
        
        $supplier = buildSupplier($company);
        
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
            $credentials
        );

        updateCompanyStatus(COMPANY_STATUS_PRODUCTION);
        
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
        $company = loadCurrentCompany();
    
        $this->company = $company;
        $this->settings = getCertificateSettings((int)$company['id']);
    
        if (empty($this->settings['production_certificate_content'])) {
            throw new Exception('Production certificate not found.');
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
            ->setOrganizationName($this->settings['organization_name'])
            ->setOrganizationalUnitName($this->settings['branch_name'])
            ->setAddress($this->settings['address'])
            ->setInvoiceType($this->settings['invoice_type'])
            ->setEnvironment($this->settings['environment'])
            ->setBusinessCategory($this->settings['business_category'])
            ->generateAndSave(
                $csrPath,
                $keyPath
            );
    
        $this->settings['generated_uuid'] = $uuid;
        $this->settings['generated_at'] = date('Y-m-d H:i:s');
        $this->settings['status'] = 'generated';
    
        saveCertificateSettings($this->settings);
    
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
     * 
     */
    public function renewProductionCertificate(string $otp): array
    {
        if (trim($otp) === '') {
            throw new Exception('OTP is required.');
        }
    
        $company = loadCurrentCompany();
    
        $credentials = loadProductionCredentials();
    
        $csrPath = getCSRFile();
    
        if (!file_exists($csrPath)) {
            throw new Exception('Renewal CSR not found.');
        }
    
        $csr = file_get_contents($csrPath);
    
        if ($csr === false) {
            throw new Exception('Unable to read Renewal CSR.');
        }
    
        $api = new ZatcaAPI(getApiEnvironment());
    
        $result = $api->renewProductionCertificate(
            $credentials['binary_security_token'],
            $credentials['secret'],
            $csr,
            trim($otp)
        );
    
        $this->backupCertificateFiles(
            false,
            false,
            true
        );
    
        saveProductionCredentials(
            $result->getCertificate(),
            $result->getSecret(),
            $result->getRequestId()
        );
    
        updateCertificateValidity(
            (int)$company['id'],
            $result->getCertificate()
        );
    
        return [
            'success' => true,
            'message' => 'Production certificate renewed successfully.',
            'data' => [
                'request_id' => $result->getRequestId()
            ]
        ];
    }

}
