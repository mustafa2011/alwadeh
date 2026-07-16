<?php
namespace App\Services;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use \Saleh7\Zatca\GeneratorInvoice;
use \Saleh7\Zatca\InvoiceSigner;
use \Saleh7\Zatca\Helpers\Certificate;
use Exception;

class InvoiceService
{
    protected InvoiceMapper $invoiceMapper;
    protected array $company = [];
    protected array $settings = [];
    protected string $companyPath = '';
    protected array $productionCredentials = [];
    protected array $complianceCredentials = [];

    public function __construct() {
        $this->invoiceMapper = new InvoiceMapper();
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
    private array $invoiceState = [];

    private function getInvoiceStateFile(): string {
        return $this->companyPath . DIRECTORY_SEPARATOR . 'invoice_state.json';
    }
    private function getInvoicesDirectory(): string
    {
        $path = $this->companyPath . DIRECTORY_SEPARATOR . 'invoices';
    
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    
        return $path;
    }    
    private function loadInvoiceState(): void {
        $file = $this->getInvoiceStateFile();
        if (!file_exists($file)) {
            $this->invoiceState = [
                'last_icv' => 0,
                'last_invoice_hash' => '',
                'last_uuid' => '',
                'updated_at' => '',
            ];
            saveJsonFile($file, $this->invoiceState);
            return;
        }
        $this->invoiceState = loadJsonFile($file);
    }

    private function getInvoiceState(): array {
        return $this->invoiceState;
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
        if (empty($this->getProductionCredentials()['certificate'])) {
            throw new Exception('Production certificate not found.');
        }

        if (empty($this->getProductionCredentials()['secret'])) {
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
        $privateKey = loadPrivateKey();
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
        $this->loadCurrentCompany();
        $this->loadInvoiceState();
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
        $privateKey = loadPrivateKey();
        $package = buildSignedInvoice(
            $invoice,
            $credentials,
            $privateKey,
            $this->getInvoicesDirectory()
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
                $this->getInvoiceStateFile(),           
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
    private function prepareSupplier(): array {
        return buildSupplier($this->getSettings());
    }
    private function prepareInvoiceData(string $type, array $invoiceData): array {
        $chain = getNextInvoiceChain($this->getInvoiceStateFile());
        $invoice = array_replace_recursive(
            [
                'invoice_type' => $type,
                'supplier' => $this->prepareSupplier(),
                'environment' => $this->getSettings()['environment'] ?? null,
                'invoice_state' => $this->invoiceState,
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
    public function signInvoice(string $xmlPath): array {
        $this->loadCurrentCompany();
        $this->validateSigningRequirements();
        return $this->signInvoiceXml($xmlPath);
    }

    public function processInvoice(array $invoiceData): array {
        $result = $this->createInvoice($invoiceData);
        $signed = $this->signInvoiceXml(
            $result['data']['xml_path']
        );
        return [
            'create' => $result,
            'sign'   => $signed,
        ];
    }
}