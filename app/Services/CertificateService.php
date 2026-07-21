<?php

namespace App\Services;
use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\ZatcaAPI;
use App\Repositories\CompanySettingsRepository;
use App\Repositories\CompanyStorageRepository;
use App\Repositories\CertificateStorageRepository;
use App\Services\CompanyService;
use App\Services\ComplianceService;
use App\Validators\CertificateValidator;

class CertificateService
{
    protected CompanySettingsRepository $settingsRepository;
    protected CompanyStorageRepository $storageRepository;
    protected CertificateStorageRepository $certificateStorageRepository;
    protected CompanyService $companyService;
    protected ComplianceService $complianceService;
    protected CertificateValidator $certificateValidator;
    protected array $company = [];

    public function __construct()
    {
        $this->settingsRepository = new CompanySettingsRepository();
        $this->storageRepository = new CompanyStorageRepository();
        $this->certificateStorageRepository = new CertificateStorageRepository();
        $this->companyService = new CompanyService();
        $this->complianceService = new ComplianceService();
        $this->certificateValidator = new CertificateValidator();
    } 

    /**
     * Generate CSR and Private Key.
     * @param array $data
     * @return array
     */
    public function generateCSR(array $data): array
    {
        $this->certificateValidator->validateCSR($data);
    
        $this->company = $this->storageRepository->initializeCompany([
            'crn'          => $data['crn'] ?? '',
            'vat'          => $data['organization_identifier'] ?? '',
            'company_name' => $data['organization_name'] ?? '',
            'branch_name'  => $data['organizational_unit_name'] ?? '',
            'environment'  => $data['environment'] ?? 'nonprod'
        ]);
        
        $this->storageRepository->syncCertificateCompanyData((int)$this->company['id'], $data);
        
        $environment = $data['environment'] ?? 'nonprod';
    
        $uuid = generateUUID();
    
        $commonName = getCommonNameByEnvironment($environment);
   
        $this->storageRepository->backupCertificateFiles();
    
        $this->storageRepository->buildCertificate($data, $uuid, $commonName);

        $csrPath = $this->storageRepository->csrPath($this->company['crn']);
        $keyPath = $this->storageRepository->privateKeyPath($this->company['crn']);
    
        $settings = [
            'company_id'         => (int) $this->company['id'],
            'environment'        => $environment,
        
            'vat_number'         => $this->company['tax_scheme']['company_id_value'] ?? '',
            'crn'                => $this->company['commercial_registration_number'] ?? '',
            'organization_name'  => $this->company['registration_name'] ?? '',
            'branch_name'        => $data['organizational_unit_name'],
            'address'            => $data['address'],
        
            'street'             => $this->company['address']['street_name'] ?? '',
            'building_number'    => $this->company['address']['building_number'] ?? '',
            'subdivision'        => $this->company['address']['city_subdivision_name'] ?? '',
            'city'               => $this->company['address']['city_name'] ?? '',
            'postal_zone'        => $this->company['address']['postal_zone'] ?? '',
        
            'business_category'  => $data['business_category'],
            'invoice_type'       => $data['invoice_type'],
        
            'common_name'        => $commonName,
            'serial_1'           => $data['serial_1'],
            'serial_2'           => $data['serial_2'],
            'generated_uuid'     => $uuid,
            'serial_number'      => $data['serial_1'].'|'.$data['serial_2'].'|'.$uuid,
            'certificate_name'   => $data['serial_1'].'_'.$data['serial_2'].'_'.$uuid,            
            'csr_content'        => $this->storageRepository->loadCSR($this->company['crn']),
            'private_key_content' => $this->storageRepository->loadPK($this->company['crn']),
            'status'             => 'generated',            
            'generated_at'       => date('Y-m-d H:i:s'),
        
            'certificate_path'   => $csrPath,
            'private_key_path'   => $keyPath
        ];

        $this->settingsRepository->save($settings);    
        $this->storageRepository->updateCompanyStatus(COMPANY_STATUS_CSR);

        
        return [
            'success' => true,
            'message' => 'Certificate generated successfully.'
        ];
    }

    /**
     * Request compliance certificate from ZATCA.
     * @param string $otp
     * @return array
     */
    public function requestComplianceCertificate(string $otp): array
    {
        $this->company = $this->storageRepository->loadCurrentCompany();

        $csrPath = $this->storageRepository->csrPath($this->company['crn']);

        $environment = getApiEnvironment();
    
        $api = $this->complianceService->createComplianceApi($environment);
    
        $csr = $api->loadCSRFromFile($csrPath);
    
        $result = $api->requestComplianceCertificate($csr, trim($otp));
    
        $this->settingsRepository->updateCompliance(
            (int)$this->company['id'],
            $result->getRequestId(),
            $result->getCertificate(),
            $result->getSecret()
        );
  
        updateCertificateValidity(
            (int)$this->company['id'],
            $result->getCertificate()
        );
        
        $this->storageRepository->backupCertificateFiles(false, false);
        
        $privateKey = $this->storageRepository->loadPK($this->company['crn']);
        
        saveComplianceCertificate(
            $result,
            $csr,
            $privateKey
        );
    
        $this->storageRepository->updateCompanyStatus(COMPANY_STATUS_COMPLIANCE);
    
        $this->storageRepository->updateCurrentCompany([
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
     */
    public function runComplianceCheck(): array
    {

        $credentials = $this->certificateStorageRepository->loadComplianceCredentials();
       
        $environment = getApiEnvironment();
        
        $supplier = $this->companyService->buildSupplier();
        
        $privateKey = $this->certificateStorageRepository->loadPrivateKey();
        
        $outputDirectory = getComplianceOutputDirectory();
    
        $api = $this->complianceService->createComplianceApi($environment);
    
        $testInvoices = getComplianceInvoices($supplier);
    
        $results = [];
        
        $icv = 0;
    
        foreach ($testInvoices as $test) {
    
            $icv++;
    
            $invoice = $test['data'];
    
            $invoice['additionalDocuments'][0]['uuid'] = (string) $icv;
    
            $result = $this->complianceService->processComplianceInvoice(
                $api,
                $invoice,
                $credentials,
                $privateKey,
                $outputDirectory,
                $icv
            );
    
            $results[] = $result;
    
        }
        
        $production = $this->complianceService->requestProductionCertificate(
            $api,
            $credentials
        );

        $this->storageRepository->updateCompanyStatus(COMPANY_STATUS_PRODUCTION, true);
        
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
     */
    public function generateRenewCSR(): array
    {
        $this->company = $this->storageRepository->loadCurrentCompany();

        $csrPath = $this->storageRepository->csrPath($this->company['crn']);

        $settings = $this->storageRepository->getCompanyZatcaSettings(); //  getCertificateSettings((int)$this->company['id']);
    
        $this->storageRepository->backupCertificateFiles();
    
        $uuid = generateUUID();
    
        $commonName = getCommonNameByEnvironment(
            $settings['environment']
        );
                
        $keyPath = $this->storageRepository->privateKeyPath($this->company['crn']);
    
        (new CertificateBuilder())
            ->setOrganizationIdentifier($settings['vat_number'])
            ->setSerialNumber(
                $settings['serial_1'],
                $settings['serial_2'],
                $uuid
            )
            ->setCommonName($commonName)
            ->setCountryName('SA')
            ->setOrganizationName($settings['organization_name'])
            ->setOrganizationalUnitName($settings['branch_name'])
            ->setAddress($settings['address'])
            ->setInvoiceType($settings['invoice_type'])
            ->setEnvironment($settings['environment'])
            ->setBusinessCategory($settings['business_category'])
            ->generateAndSave(
                $csrPath,
                $keyPath
            );
    
        $settings['generated_uuid'] = $uuid;
        $settings['generated_at'] = date('Y-m-d H:i:s');
        $settings['status'] = 'generated';
    
        saveCertificateSettings($settings);
    
        return [
            'success' => true,
            'message' => 'Renewal CSR generated successfully.'
        ];
    }

    /**
     * Renew Production Certificate.
     * @param string $otp
     * @return array
     */
    public function renewProductionCertificate(string $otp): array
    {
        $this->company = $this->storageRepository->loadCurrentCompany();
        
        $crn = $this->company['crn'];
            
        $credentials = $this->certificateStorageRepository->loadProductionCredentials();
    
        $csr = $this->storageRepository->loadCSR($this->company['crn']);

    
        $api = new ZatcaAPI(getApiEnvironment());
    
        $result = $api->renewProductionCertificate(
            $credentials['certificate'],
            $credentials['secret'],
            $csr,
            trim($otp)
        );
    
        $this->storageRepository->backupCertificateFiles(false, false);
    
        saveProductionCredentials($result);
    
        updateCertificateValidity(
            (int)$this->company['id'],
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