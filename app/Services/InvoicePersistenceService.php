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
        array $invoiceData = []
    ): int {
        $this->db->beginTransaction();
          
        try {           
            $invoiceId = $this->invoiceRepository->create([
                'company_id' => $company['id'],
                'customer_id' => $invoiceData['customerId'],
                'invoice_number' => $invoice['id'],
                'invoice_uuid' => $package['uuid'],
                'invoice_type' => $invoice['invoiceType']['type'] ?? 'invoice',
                'invoice_kind' => $invoice['invoice_type'] ?? 'simplified',
                'issue_date' => $invoice['issueDate'],
                'supply_date' => $invoice['issueDate'],
                'issue_time' => $invoice['issueDate'] . ' ' . $invoice['issueTime'],
                'currency_code' => $invoice['currencyCode'] ?? 'SAR',
                'document_currency_code' => $invoice['currencyCode'] ?? 'SAR',
                'tax_currency_code' => $invoice['taxCurrencyCode'] ?? 'SAR',
                'icv' => $chain['icv'],
                'previous_invoice_hash' => $chain['previous_hash'] ?? null,
                'invoice_hash' => $package['hash'],
                'xml_file_path' => $package['xml_path'],
                'signed_xml_file_path' => $package['signed_xml_path'],
                'invoice_status' => ($invoice['invoice_type'] === 'standard')
                    ? 'cleared'
                    : 'reported',
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
}