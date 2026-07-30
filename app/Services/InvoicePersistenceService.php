<?php
namespace App\Services;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceZatcaRepository;
use App\Repositories\InvoiceTotalsRepository;
use App\Repositories\InvoiceTaxTotalsRepository;
use App\Repositories\InvoiceLineRepository;
use App\Repositories\InvoiceSnapshotRepository;
use App\Core\Database;
use PDO;

class InvoicePersistenceService
{
    private PDO $db;
    protected InvoiceRepository $invoiceRepository;
    protected InvoiceZatcaRepository $invoiceZatcaRepository;
    protected InvoiceTotalsRepository $invoiceTotalsRepository;
    protected InvoiceTaxTotalsRepository $invoiceTaxTotalsRepository;
    protected InvoiceLineRepository $invoiceLineRepository;
    protected InvoiceSnapshotRepository $invoiceSnapshotRepository;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->invoiceRepository = new InvoiceRepository($this->db);
        $this->invoiceZatcaRepository = new InvoiceZatcaRepository($this->db);
        $this->invoiceTotalsRepository = new InvoiceTotalsRepository($this->db);
        $this->invoiceTaxTotalsRepository = new InvoiceTaxTotalsRepository($this->db);
        $this->invoiceLineRepository = new InvoiceLineRepository($this->db);
        $this->invoiceSnapshotRepository=new InvoiceSnapshotRepository($this->db);
    }

    public function save(
        array $invoice,
        array $package,
        array $chain,
        array $company,
        array $submitResult,
        array $invoiceData
    ): int {
        $this->db->beginTransaction();
        try {
            if (($submitResult['status'] ?? null) === 'draft') {
                $status = 'signed';
            } elseif (
                ($submitResult['submission_type'] ?? null) === 'reporting'
                && $submitResult['success']
            ) {
                $status = 'reported';
            } elseif (
                ($submitResult['submission_type'] ?? null) === 'clearance'
                && $submitResult['success']
            ) {
                $status = 'cleared';
            } else {
                $status = 'rejected';
            }          

            $invoiceId = $this->invoiceRepository->create([
                'company_id' => $company['id'],
                'customer_id' => $invoice['invoiceType']['invoice'] === 'standard'
                    ? ($invoiceData['customerId'] ?? null)
                    : null,
                'invoice_number' => $invoice['id'],
                'invoice_uuid' => $package['uuid'],
                'invoice_type' => $invoice['invoiceType']['type'] ?? 'invoice',
                'invoice_kind' => $invoice['invoiceType']['invoice'] ?? 'simplified',
                'issue_date' => $invoice['issueDate'],
                'supply_date' => $invoice['issueDate'],
                'issue_time' => $invoice['issueTime'],
                'currency_code' => $invoice['currencyCode'] ?? 'SAR',
                'document_currency_code' => $invoice['currencyCode'] ?? 'SAR',
                'tax_currency_code' => $invoice['taxCurrencyCode'] ?? 'SAR',
                'icv' => $chain['icv'],
                'previous_invoice_hash' => $chain['previous_hash'] ?? null,
                'invoice_hash' => $package['hash'],
                'xml_file_path' => $package['xml_path'],
                'signed_xml_file_path' => $package['signed_xml_path'],
                'invoice_status' => $status,
                'qr_code' => $package['qr_code'] ?? null,
                'created_by' => $_SESSION['user']['id'] ?? null,
            ]);         
            
            $this->invoiceZatcaRepository->create(
                $invoiceId,
                $package,
                $chain,
                $submitResult
            );
    
            $this->invoiceTotalsRepository->create(
                $invoiceId,
                $invoice['legalMonetaryTotal']
            );
    
            $this->invoiceTaxTotalsRepository->create(
                $invoiceId,
                $invoice['taxTotal']
            );
    
            $this->invoiceLineRepository->create(
                $invoiceId,
                $invoice['invoiceLines']
            );
    
            $this->invoiceSnapshotRepository->create(
                $invoiceId,
                $invoice,
                $invoiceData
            );
    
            $this->db->commit();
    
            return $invoiceId;
    
        } catch (\Throwable $e) {
    
            $this->db->rollBack();
    
            throw $e;
    
        }
    }
    public function update(
        int $invoiceId,
        array $invoice,
        array $package,
        array $chain,
        array $invoiceData
    ): void {
        $this->db->beginTransaction();
        try {
            $this->invoiceRepository->update(
                $invoiceId,
                [
                    'customer_id' => $invoice['invoiceType']['invoice'] === 'standard'
                        ? ($invoiceData['customerId'] ?? null)
                        : null,
                    'invoice_kind' => $invoice['invoiceType']['invoice'] ?? 'simplified',
                    'issue_date' => $invoice['issueDate'],
                    'issue_time' => $invoice['issueTime'],
                    'currency_code' => $invoice['currencyCode'] ?? 'SAR',
                    'document_currency_code' => $invoice['currencyCode'] ?? 'SAR',
                    'tax_currency_code' => $invoice['taxCurrencyCode'] ?? 'SAR',
                    'invoice_hash' => $package['hash'],
                    'xml_file_path' => $package['xml_path'],
                    'signed_xml_path' => $package['signed_xml_path'],
                    'qr_code' => $package['qr_code'] ?? null
                ]
            );
            $this->invoiceLineRepository->deleteByInvoiceId($invoiceId);
            $this->invoiceTotalsRepository->deleteByInvoiceId($invoiceId);
            $this->invoiceTaxTotalsRepository->deleteByInvoiceId($invoiceId);
            $this->invoiceSnapshotRepository->deleteByInvoiceId($invoiceId);            
            $this->invoiceLineRepository->create(
                $invoiceId,
                $invoice['invoiceLines']
            );
            $this->invoiceTotalsRepository->create(
                $invoiceId,
                $invoice['legalMonetaryTotal']
            );
            $this->invoiceTaxTotalsRepository->create(
                $invoiceId,
                $invoice['taxTotal']
            );
            $this->invoiceSnapshotRepository->create(
                $invoiceId,
                $invoice,
                $invoiceData
            );            
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
        
}