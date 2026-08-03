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
use App\Repositories\ItemRepository;
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
    private ItemRepository $itemRepository;
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
        $this->customerRepository = new CustomerRepository();
        $this->itemRepository = new ItemRepository();
    }
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
        $invoiceData = $this->prepareInvoiceData(
            $type,
            $invoiceData
        );           
        $result = $this->buildInvoicePackage(
            $type,
            $invoiceData,
            $chain,
            $getSettings
        );
        
        $invoice = $result['invoice'];
        $package = $result['package'];        
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
    private function prepareItems(array $items): array
    {
        $company = $this->storageRepository->loadCurrentCompany();
        foreach ($items as &$item) {
            $itemId = (int)($item['itemId'] ?? $item['item_id'] ?? 0);
            $itemData = $itemId > 0
                ? $this->itemRepository->find((int)$company['id'], $itemId)
                : null;
            if (!$itemData) {
                $itemData = [
                    'id' => $item['id'] ?? 0,
                    'item_name' => $item['itemName'] ?? '',
                    'unit_id' => null,
                    'tax_percent' => $item['tax_percent'] ?? 15
                ];
            }
            $this->invoiceValidator->validateItem($item);
            $item['id'] = $itemData['id'];
            $item['name'] = $itemData['item_name'];
            $item['unitCode'] = $itemData['unit_id']
                ? 'PCE'
                : 'C62';
            $item['taxCategory'] = [
                'id' => 'S',
                'percent' => (float)($itemData['tax_percent'] ?? 15)
            ];
            if (!empty($item['discount']['value'])) {
                $unitPrice = (float)($item['unitPrice'] ?? 0);
                if ($item['discount']['type'] === 'percent') {
                    $item['price']['allowanceCharges'] = [
                        [
                            'isCharge' => false,
                            'reason' => 'Discount',
                            'amount' => ($unitPrice * $item['discount']['value'] / 100),
                            'baseAmount' => $unitPrice,
                            'percentage' => (float)$item['discount']['value'],
                            'taxCategory' => $item['taxCategory']
                        ]
                    ];
                } else {
                    $item['price']['allowanceCharges'] = [
                        [
                            'isCharge' => false,
                            'reason' => 'Discount',
                            'amount' => (float)$item['discount']['value'],
                            'baseAmount' => $unitPrice,
                            'percentage' => 0,
                            'taxCategory' => $item['taxCategory']
                        ]
                    ];
                }
            }
        }
        return $items;
    } 
    public function updateInvoice(int $invoiceId,array $invoiceData,bool $submit=false): array
    {
        $oldInvoice=$this->invoiceRepository->findById($invoiceId);
        $invoiceData['invoiceNumber']=$oldInvoice['invoice_number'];
        $invoiceData['invoiceType']=[
            'invoiceKind'=>$oldInvoice['invoice_kind'],
            'invoiceType'=>$oldInvoice['invoice_type']
        ];
        $invoiceData['customerId']=$oldInvoice['customer_id'];       
        $this->invoiceValidator->validateUpdateInvoice($oldInvoice);
        $company=$this->storageRepository->loadCurrentCompany();
        $settings=$this->companySettingsRepository->loadSettings();
        $type=$this->invoiceValidator->getInvoiceType($invoiceData);
        $invoiceData = $this->prepareInvoiceData(
            $type,
            $invoiceData
        );
        $chain=[
            'icv'=>$oldInvoice['icv'],
            'previous_hash'=>$oldInvoice['previous_invoice_hash']
        ];
        $result = $this->buildInvoicePackage(
            $type,
            $invoiceData,
            $chain,
            $settings,
            [
                'uuid' => $oldInvoice['invoice_uuid'],
                'id' => $oldInvoice['invoice_number']
            ]
        );
        $invoice = $result['invoice'];
        $package = $result['package'];        
        $this->invoicePersistenceService->update(
            $invoiceId,
            $invoice,
            $package
        );
        return [
            'success'=>true,
            'invoice_id'=>$invoiceId,
            'message'=>'Invoice updated successfully.'
        ];
    }   
    private function normalizeAllowanceCharges(array $invoiceData): array
    {
        $invoiceData['allowanceCharges'] = array_values(array_filter(
            $invoiceData['allowanceCharges'] ?? [],
            function ($row) {
                return !empty($row['value']) && (float)$row['value'] > 0;
            }
        ));
        foreach ($invoiceData['allowanceCharges'] as &$row) {
            if (empty($row['reason'])) {
                $row['reason'] = !empty($row['chargeIndicator'])
                    ? 'Charge'
                    : 'Allowance';
            }
        }
        return $invoiceData;
    } 
    private function prepareInvoiceData(
        string $type,
        array $invoiceData
    ): array
    {
        if ($type === 'standard') {
            $invoiceData['customer'] = $this->customerRepository->findForInvoice(
                $invoiceData['customerId']
            );
        } else {
            $invoiceData['customer'] = [];
        }
        $invoiceData['items'] = $this->prepareItems(
            $invoiceData['items'] ?? []
        );
        return $this->normalizeAllowanceCharges($invoiceData);
    }
    private function buildInvoicePackage(
        string $type,
        array $invoiceData,
        array $chain,
        array $settings,
        ?array $fixedHeader = null
    ): array
    {
        $invoice = $this->invoiceBuilder->prepare(
            $type,
            $this->companyService->buildSupplier(),
            $settings['environment'] ?? null,
            $chain,
            $invoiceData
        );
        if ($fixedHeader) {
            $invoice['uuid'] = $fixedHeader['uuid'];
            $invoice['id'] = $fixedHeader['id'];
        }
        $totals = $this->invoiceCalculationService->calculate(
            $invoiceData['items'],
            $invoiceData['allowanceCharges']
        );
        $invoice = $this->invoiceBuilder->build(
            $invoice,
            $totals
        );
        $package = $this->invoiceXmlService->buildPackage(
            $invoice,
            $this->storageRepository->getInvoicesDirectory()
        );
        return [
            'invoice' => $invoice,
            'package' => $package
        ];
    }             
}