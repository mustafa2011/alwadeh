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
        $this->companySettingsRepository = new CompanySettingsRepository();
    }

    // public function createInvoice(array $invoiceData): array {
    //     return $this->issueInvoice($invoiceData, false);
    // }
    public function createInvoice(array $invoiceData): array
    {
        return $this->issueInvoice(
            $invoiceData,
            $invoiceData['submit'] ?? true
        );
    }
    
    public function issueInvoice(array $invoiceData, bool $submit = true): array  {

        $getSettings = $this->companySettingsRepository->loadSettings();
        $company = $this->storageRepository->loadCurrentCompany();

        $this->invoiceValidator->validateGenerationRequirements( $company, $getSettings);
        $type = $this->invoiceValidator->getInvoiceType($invoiceData);

        $chain = $this->invoiceChainService->next($company['id']);        

        if ($type === 'standard') {
            $invoiceData['customer'] = $this->customerRepository->findForInvoice(
                $invoiceData['customerId']
            );
        } else {
            $invoiceData['customer'] = [];
        }           
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

        $package = $this->invoiceXmlService->buildPackage(
            $invoice,
            $this->storageRepository->getInvoicesDirectory()
        );

        $api = $this->invoiceChainService->api();

        $submitResult = null;

        if ($submit) {
            $api = $this->invoiceChainService->api();
            $submitResult = $this->invoiceSubmissionService->submit(
                $api,
                $package
            );
        } else {
            $submitResult = [
                'success' => true,
                'status' => 'draft'
            ];
        }
        
        $isSimplified = ($type === 'simplified');
        if (
            $submit &&
            !$isSimplified &&
            !empty($submitResult['cleared_xml'])
        ) {                           
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
        $resultSaved = [
            'invoice_kind' => $invoice['invoiceType']['invoice'] ?? null,
            'customer' => $invoiceData['customerId'] ?? null
        ];                  
        return [
            'success' => $submitResult['success'],
            'message' =>
                ($submitResult['status'] ?? null) === 'draft'
                    ? 'Draft invoice created successfully.'
                    : (
                        ($submitResult['submission_type'] ?? null) === 'clearance'
                            ? (
                                $submitResult['success']
                                    ? 'Invoice cleared successfully.'
                                    : 'Invoice clearance failed.'
                            )
                            : (
                                $submitResult['success']
                                    ? 'Invoice submitted successfully.'
                                    : 'Invoice submission failed.'
                            )
                    ),            
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

}