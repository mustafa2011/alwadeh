<?php

namespace App\Services;

use App\Repositories\CertificateStorageRepository;
use App\Repositories\CompanyStorageRepository;
use Saleh7\Zatca\ZatcaAPI;
use Saleh7\Zatca\Api\ProductionCertificateResult;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;
use App\Services\InvoiceXmlService;
use Exception;

class ComplianceService
{
    protected CertificateStorageRepository $certificateStorageRepository;
    protected CompanyStorageRepository $companyStorageRepository;
    protected InvoiceXmlService $invoiceXmlService;
    public function __construct()
    {
        $this->companyStorageRepository = new CompanyStorageRepository();
        $this->certificateStorageRepository = new CertificateStorageRepository($this->companyStorageRepository);
        $this->invoiceXmlService = new InvoiceXmlService();
    }

    function requestProductionCertificate(
        ZatcaAPI $api,
        array $credentials
    ): ProductionCertificateResult {
    
        $result = $api->requestProductionCertificate(
            $credentials['certificate'],
            $credentials['secret'],
            $credentials['request_id']
        );
    
        $this->certificateStorageRepository->saveProductionCredentials($result);
    
        return $result;
    }    
    
    function createComplianceApi(string $environment)
    {
        return new ZatcaAPI($environment);
    }

    private function signInvoice(
        string $xmlFile,
        array $credentials,
        string $privateKey,
        string $signedFileName,
        string $outputDirectory
    ) {
    
        $xmlContent = file_get_contents($xmlFile);
    
        if ($xmlContent === false) {
            throw new Exception("Unable to read invoice XML: {$xmlFile}");
        }
    
        $certificate = new Certificate(
            $credentials['certificate'],
            $privateKey,
            $credentials['secret']
        );
    
        $signer = InvoiceSigner::signInvoice(
            $xmlContent,
            $certificate
        );
    
        $signer->saveXMLFile(
            $signedFileName,
            $outputDirectory
        );
    
        return [
    
            // Signed invoice XML content
            'signedXml' => $signer->getInvoice(),
    
            // Invoice hash
            'hash' => $signer->getHash(),
    
            // Original XML file
            'xmlFile' => $xmlFile,
    
            // Signed XML file path
            'signedXmlFile' =>
                $outputDirectory
                . DIRECTORY_SEPARATOR
                . $signedFileName,
    
        ];
    }
    
    private function validateComplianceInvoice(
        ZatcaAPI $api,
        array $credentials,
        string $signedXml,
        string $invoiceHash,
        string $uuid
    ) {
    
        $result = $api->validateInvoiceCompliance(
    
            $credentials['certificate'],
    
            $credentials['secret'],
    
            $signedXml,
    
            $invoiceHash,
    
            $uuid
    
        );
            
        return [
    
            'success' =>
    
                $result->isSuccess()
                || $result->getStatusCode() == 202,
    
            'statusCode' => $result->getStatusCode(),
    
            'status' =>
    
                $result->getValidationStatus()
                ?? 'UNKNOWN',
    
            'warnings' =>
    
                $result->getWarningMessages(),
    
            'errors' =>
    
                $result->getErrorMessages(),
    
            'response' => $result,
    
        ];
    
    }
    
    public function processComplianceInvoice(
        ZatcaAPI $api,
        array $invoiceData,
        array $credentials,
        string $privateKey,
        string $outputDirectory,
        int $icv
    ) {
    
        // Update ICV
        $invoiceData['additionalDocuments'][0]['uuid'] = (string) $icv;
    
        // Generate XML
        $xmlFile = $this->invoiceXmlService->generateInvoiceXml(
            $invoiceData,
            $outputDirectory
        );
    
        // Sign XML
        $signed = $this->signInvoice(
            $xmlFile,
            $credentials,
            $privateKey,
            $invoiceData['id'] . '_signed.xml',
            $outputDirectory
        );
    
        // Validate
        $validation = $this->validateComplianceInvoice(
            $api,
            $credentials,
            $signed['signedXml'],
            $signed['hash'],
            $invoiceData['uuid']
        );
    
        return [
    
            'success' => $validation['success'],
    
            'status' => $validation['status'],
    
            'statusCode' => $validation['statusCode'],
    
            'warnings' => $validation['warnings'],
    
            'errors' => $validation['errors'],
    
            'uuid' => $invoiceData['uuid'],
    
            'invoiceId' => $invoiceData['id'],
    
            'hash' => $signed['hash'],
    
            'xmlFile' => $signed['xmlFile'],
    
            'signedXmlFile' => $signed['signedXmlFile'],
    
        ];
    
    }

    public function getComplianceInvoices(array $supplier)
    {
        return [
    
            [
                'label' => '1/6 Standard Invoice',
    
                'data' => (new \App\Builders\InvoiceBuilder())->buildInvoice($supplier, [
    
                    'id' => 'STD00001',
    
                    'type' => 'standard',
    
                    'subtype' => 'invoice',
    
                    'hasCustomer' => true,
    
                    'hasDelivery' => true,
    
                ]),
    
            ],
    
            [
                'label' => '2/6 Standard Credit Note',
    
                'data' => (new \App\Builders\InvoiceBuilder())->buildInvoice($supplier, [
    
                    'id' => 'STD00002',
    
                    'type' => 'standard',
    
                    'subtype' => 'credit',
    
                    'hasCustomer' => true,
    
                    'hasDelivery' => true,
    
                    'billingRef' => 'STD00002',
    
                ]),
    
            ],
    
            [
                'label' => '3/6 Standard Debit Note',
    
                'data' => (new \App\Builders\InvoiceBuilder())->buildInvoice($supplier, [
    
                    'id' => 'STD00003',
    
                    'type' => 'standard',
    
                    'subtype' => 'debit',
    
                    'hasCustomer' => true,
    
                    'hasDelivery' => true,
    
                    'billingRef' => 'STD00003',
    
                ]),
    
            ],
    
            [
                'label' => '4/6 Simplified Invoice',
    
                'data' => (new \App\Builders\InvoiceBuilder())->buildInvoice($supplier, [
    
                    'id' => 'SIM00004',
    
                    'type' => 'simplified',
    
                    'subtype' => 'invoice',
    
                ]),
    
            ],
    
            [
                'label' => '5/6 Simplified Credit Note',
    
                'data' => (new \App\Builders\InvoiceBuilder())->buildInvoice($supplier, [
    
                    'id' => 'SIM00005',
    
                    'type' => 'simplified',
    
                    'subtype' => 'credit',
    
                    'billingRef' => 'SIM00005',
    
                ]),
    
            ],
    
            [
                'label' => '6/6 Simplified Debit Note',
    
                'data' => (new \App\Builders\InvoiceBuilder())->buildInvoice($supplier, [
    
                    'id' => 'SIM00006',
    
                    'type' => 'simplified',
    
                    'subtype' => 'debit',
    
                    'billingRef' => 'SIM00006',
    
                ]),
    
            ],
    
        ];
    }      
}