<?php
namespace App\Services;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use App\Repositories\CompanyStorageRepository;
use \Saleh7\Zatca\InvoiceSigner;
use \Saleh7\Zatca\Helpers\Certificate;
use App\Services\ComplianceService;
use App\Services\CompanyService;
use App\Repositories\CertificateStorageRepository;
use App\Validators\InvoiceValidator;
use App\Builders\InvoiceBuilder;
use App\Repositories\InvoiceRepository;
use App\Services\InvoicePersistenceService;
use App\Core\Database;
use PDO;
use Exception;

class InvoiceService
{
    private PDO $db;
    protected InvoicePersistenceService $invoicePersistenceService;
    protected InvoiceRepository $invoiceRepository;
    protected InvoiceBuilder $invoiceBuilder;
    protected InvoiceValidator $invoiceValidator;
    protected CertificateStorageRepository $certificateRepository;
    protected InvoiceMapper $invoiceMapper;
    protected CompanyStorageRepository $storageRepository;
    protected array $company = [];
    protected array $settings = [];
    protected string $companyPath = '';
    protected array $productionCredentials = [];
    protected array $complianceCredentials = [];
    protected ComplianceService $complianceService;
    protected CompanyService $companyService;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->invoiceValidator = new InvoiceValidator();
        $this->invoiceMapper = new InvoiceMapper();
        $this->invoiceBuilder = new InvoiceBuilder();        
        $this->complianceService = new ComplianceService();
        $this->companyService = new CompanyService();
        $this->storageRepository = new CompanyStorageRepository();
        $this->company = $this->storageRepository->loadCurrentCompany();
        $this->certificateRepository = new CertificateStorageRepository();
        $this->settings = $this->certificateRepository->loadSettings();
        $this->productionCredentials = $this->certificateRepository->loadProductionCredentials();
        $this->complianceCredentials = $this->certificateRepository->loadComplianceCredentials();
        $this->invoiceRepository = new InvoiceRepository($this->db);
        $this->invoicePersistenceService = new InvoicePersistenceService();      
    }

    private function getCompany(): array {
        return $this->company;
    }
    private function getSettings(): array {
        return $this->settings;
    }
    private function getProductionCredentials(): array {
        
        return $this->productionCredentials;
    }
    private function getComplianceCredentials(): array {
        return $this->complianceCredentials;
    }

    private function createCertificate(): Certificate {
        $credentials = $this->getProductionCredentials();
        if (empty($credentials['certificate'])) {
            throw new Exception('Production certificate not found.');
        }
        if (empty($credentials['secret'])) {
            throw new Exception('Production secret not found.');
        }
        $privateKey = $this->certificateRepository->loadPrivateKey();
        if (empty($privateKey)) {
            throw new Exception('Private key not found.');
        }
        return new Certificate(
            $credentials['certificate'],
            $privateKey,
            $credentials['secret']
        );
    }

    public function createInvoice(array $invoiceData): array {
        return $this->issueInvoice($invoiceData, false);
    }

    public function issueInvoice( array $invoiceData, bool $submit = true): array  {

        $this->storageRepository->loadInvoiceState();
        $this->invoiceValidator->validateGenerationRequirements(
            $this->company,
            $this->settings
        );
        $type = $this->invoiceValidator->getInvoiceType($invoiceData);

        $chain = getNextInvoiceChain(
            $this->storageRepository->getInvoiceStateFile()
        );
        
        $invoice = $this->invoiceBuilder->prepare(
            $type,
            $this->companyService->buildSupplier(),
            $this->settings['environment'] ?? null,
            $this->storageRepository->loadInvoiceState(),
            $chain,
            $invoiceData
        );    

        if (!empty($invoiceData['items'])) {      
            $totals = calculateInvoiceTotals($invoiceData['items']);
            $invoice = buildInvoice(
                $invoice['supplier'],
                array_merge(
                    $invoice,
                    $totals
                )
            );
        }        

        unset($document);


        $credentials = $this->getProductionCredentials();

        $package = $this->buildSignedInvoice(
            $invoice,
            $this->storageRepository->getInvoicesDirectory()
        ); 
               
        $api = createInvoiceApi();
        $isSimplified = ($invoice['invoice_type'] === 'simplified');
        $submitResult = submitInvoice(
            $api,
            $credentials,
            $package['signed_xml'],
            $package['hash'],
            $package['uuid'],
            $isSimplified
        );
        if (
            !$isSimplified
            && !empty($submitResult['cleared_xml'])
        ) {        
            file_put_contents(
                dirname($package['signed_xml_path'])
                . DIRECTORY_SEPARATOR
                . $package['invoice_id']
                . '_zatca.xml',
                $submitResult['cleared_xml']
            );        }
        if ($submitResult['success']) {
            commitInvoiceChain(
                $this->storageRepository->getInvoiceStateFile(),
                $invoice,
                $chain,
                ['hash' => $package['hash']],
                $submitResult
            );
        
            $this->invoicePersistenceService->save(
                $invoice,
                $package,
                $chain,
                $this->company,
                $submitResult
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
   
    private function isSimplified(string $type): bool {
        return strtolower($type) === 'simplified';
    }
    private function isStandard(string $type): bool {
        return strtolower($type) === 'standard';
    }

    public function signInvoice(string $xmlPath): InvoiceSigner {
        $this->invoiceValidator->validateSigningRequirements(
            $this->productionCredentials
        );
        $xmlInvoice = file_get_contents($xmlPath);

        $signedInvoice = InvoiceSigner::signInvoice($xmlInvoice, $this->createCertificate());

        return $signedInvoice;
    }

    public function processInvoice(array $invoiceData): array {
        return $this->issueInvoice($invoiceData);
    }

    private function buildSignedInvoice(
        array $invoiceData,
        string $outputDirectory
    ): array {
        $xmlPath = generateInvoiceXml(
            $invoiceData,
            $outputDirectory
        );
    
        if (!$xmlPath || !file_exists($xmlPath)) {
            throw new Exception('Invoice XML generation failed.');
        }
    
        $signed = $this->signInvoice($xmlPath)
        ->saveXMLFile(
            $invoiceData['id'] . '_signed.xml',
            $outputDirectory
        );
    
        return [
            'invoice' => $invoiceData,
            'xml_path' => $xmlPath,
            'signed_xml' => $signed->getXML(),
            'signed_xml_path' => $outputDirectory . DIRECTORY_SEPARATOR . $invoiceData['id'] . '_signed.xml',
            'hash' => $signed->getHash(),
            'qr_code' => $signed->getQRCode(),
            'invoice_id' => $invoiceData['id'],
            'uuid' => $invoiceData['uuid'],
        ];        
    }
}