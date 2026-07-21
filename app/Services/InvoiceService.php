<?php
namespace App\Services;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use App\Repositories\CompanyStorageRepository;
use \Saleh7\Zatca\InvoiceSigner;
use \Saleh7\Zatca\Helpers\Certificate;
use App\Services\ComplianceService;
use App\Services\CompanyService;
use App\Repositories\CertificateStorageRepository;
use Exception;

class InvoiceService
{
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
        $this->invoiceMapper = new InvoiceMapper();
        $this->complianceService = new ComplianceService();
        $this->companyService = new CompanyService();
        $this->storageRepository = new CompanyStorageRepository();
        $this->company = $this->storageRepository->loadCurrentCompany();
        
        $this->certificateRepository = new CertificateStorageRepository();

        $this->settings = $this->certificateRepository->loadSettings();
        $this->productionCredentials = $this->certificateRepository->loadProductionCredentials();
        $this->complianceCredentials = $this->certificateRepository->loadComplianceCredentials();        
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


    private function validateGenerationRequirements(): void {
        if (empty($this->company)) {
            throw new Exception('Company is not loaded.');
        }

        if (empty($this->settings)) {
            throw new Exception('Certificate settings not found.');
        }
    }

    private function validateSigningRequirements(): void {
        if (empty($this->productionCredentials['certificate'])) {
            throw new Exception('Production certificate not found.');
        }

        if (empty($this->productionCredentials['secret'])) {
            throw new Exception('Production secret not found.');
        }
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

    private function getInvoiceType(array $invoiceData): string {
        if (
            empty($invoiceData['invoiceType']) ||
            empty($invoiceData['invoiceType']['invoice'])
        ) {
            throw new Exception('Invoice type is required.');
        }
        return strtolower($invoiceData['invoiceType']['invoice']);
    }

    public function createInvoice(array $invoiceData): array {
        return $this->issueInvoice($invoiceData, false);
    }

    public function issueInvoice( array $invoiceData, bool $submit = true): array  {

        $this->storageRepository->loadInvoiceState();
        $this->validateGenerationRequirements();
        $type = $this->getInvoiceType($invoiceData);
        $invoice = $this->prepareInvoiceData(
            $type,
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
        $chain = $invoice['invoice_chain'];
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
        
            );        
        }
        if ($submitResult['success']) {
            commitInvoiceChain(
                $this->storageRepository->getInvoiceStateFile(),           
                $invoice,
                $chain,
                ['hash' => $package['hash']],
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

    
    private function prepareInvoiceData(string $type, array $invoiceData): array {
        $chain = getNextInvoiceChain($this->storageRepository->getInvoiceStateFile());
        $invoice = array_replace_recursive(
            [
                'invoice_type' => $type,
                'supplier' => $this->companyService->buildSupplier(), //prepareSupplier(),
                'environment' => $this->getSettings()['environment'] ?? null,
                'invoice_state' => $this->storageRepository->loadInvoiceState(),
            ],
            $invoiceData
        );
        $invoice['type'] = $type;
        $invoice['subtype'] = strtolower($invoiceData['invoiceType']['type'] ?? 'invoice');        
        if (!isset($invoice['additionalDocuments']) || !is_array($invoice['additionalDocuments'])) {
            $invoice['additionalDocuments'] = [];
        }
        $hasICV = false;
        $hasPIH = false;
        foreach ($invoice['additionalDocuments'] as &$document) {
            if (($document['id'] ?? '') === 'ICV') {
                $document['uuid'] = (string) $chain['icv'];
                $hasICV = true;
            }
            if (($document['id'] ?? '') === 'PIH') {
                $document['attachment'] = [
                    'content' => empty($chain['previous_hash'])
                        ? getInitialPIH()
                        : $chain['previous_hash']
                ];
                $hasPIH = true;
            }
        }
        unset($document);
        if (!$hasICV) {
            $invoice['additionalDocuments'][] = [
                'id'   => 'ICV',
                'uuid' => (string) $chain['icv'],
            ];
        }
        if (!$hasPIH) {
            $invoice['additionalDocuments'][] = 
            [
                'id' => 'PIH',
                'attachment' => [
                    'content' => empty($chain['previous_hash'])
                        ? getInitialPIH()
                        : $chain['previous_hash']
                ]
            ];
        }
        $invoice['invoice_chain'] = $chain;
        return $invoice;
    }
    public function signInvoice(string $xmlPath): InvoiceSigner {
        $this->validateSigningRequirements();
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
    
        // $signedXmlPath = $outputDirectory
        //     . DIRECTORY_SEPARATOR
        //     . $invoiceData['id']
        //     . '_signed.xml';
        // $signed->saveXMLFile($signedXmlPath);
    
        return [
            'invoice' => $invoiceData,
            'xml_path' => $xmlPath,
            'signed_xml' => $signed->getXML(),
            'signed_xml_path' => $outputDirectory,
            'hash' => $signed->getHash(),
            'invoice_id' => $invoiceData['id'],
            'uuid' => $invoiceData['uuid'],
        ];
    }
}