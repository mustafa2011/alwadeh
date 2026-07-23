<?php
namespace App\Services;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use App\Repositories\CompanyStorageRepository;
use App\Services\ComplianceService;
use App\Services\CompanyService;
use App\Validators\InvoiceValidator;
use App\Builders\InvoiceBuilder;
use App\Repositories\InvoiceRepository;
use App\Services\InvoicePersistenceService;
use App\Repositories\CustomerRepository;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceChainService;
use App\Services\InvoiceSubmissionService;
use App\Services\InvoiceXmlService;
use App\Services\InvoiceSigningService;
use App\Repositories\CompanySettingsRepository;

use App\Core\Database;
use PDO;

class InvoiceService
{
    private PDO $db;
    protected InvoicePersistenceService $invoicePersistenceService;
    protected InvoiceRepository $invoiceRepository;
    protected InvoiceBuilder $invoiceBuilder;
    protected InvoiceValidator $invoiceValidator;
    protected InvoiceMapper $invoiceMapper;
    protected CompanyStorageRepository $storageRepository;
    protected ComplianceService $complianceService;
    protected CompanyService $companyService;
    private CustomerRepository $customerRepository;
    private InvoiceCalculationService $invoiceCalculationService;
    private InvoiceChainService $invoiceChainService;
    private InvoiceSubmissionService $invoiceSubmissionService;
    private InvoiceXmlService $invoiceXmlService;
    private InvoiceSigningService $invoiceSigningService;
    private CompanySettingsRepository $companySettingsRepository;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->invoiceValidator = new InvoiceValidator();
        $this->invoiceMapper = new InvoiceMapper();
        $this->invoiceBuilder = new InvoiceBuilder();        
        $this->complianceService = new ComplianceService();
        $this->companyService = new CompanyService();
        $this->storageRepository = new CompanyStorageRepository();
        $this->invoiceRepository = new InvoiceRepository($this->db);
        $this->invoicePersistenceService = new InvoicePersistenceService(); 
        $this->customerRepository = new CustomerRepository(); 
        $this->invoiceCalculationService = new InvoiceCalculationService();
        $this->invoiceChainService = new InvoiceChainService();
        $this->invoiceSubmissionService = new InvoiceSubmissionService(); 
        $this->invoiceXmlService = new InvoiceXmlService();   
        $this->invoiceSigningService = new InvoiceSigningService();
        $this->companySettingsRepository = new CompanySettingsRepository();
    }

    public function createInvoice(array $invoiceData): array {
        return $this->issueInvoice($invoiceData, false);
    }

    public function issueInvoice( array $invoiceData, bool $submit = true): array  {

        $getSettings = $this->companySettingsRepository->loadSettings();
        $company = $this->storageRepository->loadCurrentCompany();

        $this->invoiceValidator->validateGenerationRequirements( $company, $getSettings);
        $type = $this->invoiceValidator->getInvoiceType($invoiceData);

        $chain = $this->invoiceChainService->next($company['company_id']);        

        $invoiceData['customer'] =
        $this->customerRepository->findForInvoice(
            $invoiceData['customerId']
        );    
        $invoice = $this->invoiceBuilder->prepare(
            $type,
            $this->companyService->buildSupplier(),
            $getSettings['environment'] ?? null,
            $chain,
            $invoiceData
        );    
        
        if (!empty($invoiceData['items'])) {
            $totals = $this->invoiceCalculationService->calculate($invoiceData['items']);
            $invoice = $this->invoiceBuilder->build($invoice, $totals);
        }        

        $package = $this->buildSignedInvoice(
            $invoice,
            $this->storageRepository->getInvoicesDirectory()
        ); 
               
        $api = $this->invoiceChainService->api();
        $isSimplified = ($invoice['invoice_type'] === 'simplified');
        $submitResult = $this->invoiceSubmissionService->submit(
            $api,
            $package,
            $isSimplified
        );      
        if (!$isSimplified && !empty($submitResult['cleared_xml'])) {        
            file_put_contents(
                dirname($package['signed_xml_path'])
                . DIRECTORY_SEPARATOR
                . $package['invoice_id']
                . '_zatca.xml',
                $submitResult['cleared_xml']
            );        }
        if ($submitResult['success']) {                  
            $this->invoicePersistenceService->save(
                $invoice,
                $package,
                $chain,
                $company,
                $submitResult,
                $invoiceData
            );
        }
        
        return [
            'success' => $submitResult['success'],
            'message' =>
                $submitResult['success']
                    ? 'Invoice submitted successfully.'
                    : 'Invoice submission failed.',
            'data' => [
                'invoice_id' => $invoice['id'],
                'uuid' => $invoice['uuid'],
                'icv' => $chain['icv'],
                'hash' => $package['hash'],
                'xml_path' => $package['xml_path'],
                'signed_xml_path' => $package['signed_xml_path'],
                'submission' => $submitResult
            ]
        ];
    }
       
    public function processInvoice(array $invoiceData): array {
        return $this->issueInvoice($invoiceData);
    }

    private function buildSignedInvoice(
        array $invoiceData,
        string $outputDirectory
    ): array {
        $xmlPath = $this->invoiceXmlService->generate(
            $invoiceData,
            $outputDirectory
        );
    
        $signed = $this->invoiceSigningService->sign(
            $xmlPath,
            $invoiceData['id'],
            $outputDirectory
        );
        
        return [
            'invoice' => $invoiceData,
            'xml_path' => $xmlPath,
            'signed_xml' => $signed['signed_xml'],
            'signed_xml_path' => $signed['signed_xml_path'],
            'hash' => $signed['hash'],
            'qr_code' => $signed['qr_code'],
            'invoice_id' => $invoiceData['id'],
            'uuid' => $invoiceData['uuid']
        ];              
    }
}