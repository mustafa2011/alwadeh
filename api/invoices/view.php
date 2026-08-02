<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Core\Database;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceSnapshotRepository;
try {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Invalid invoice id.');
    }
    $invRepo = new InvoiceRepository(Database::getConnection());
    $invSnapshotRepo = new InvoiceSnapshotRepository(Database::getConnection());
    $invoice = $invRepo->findById($id);
    if (!$invoice) {
        throw new Exception('Invoice not found.');
    }
    if (
        ($_GET['mode'] ?? '') === 'edit'
        && $invoice['invoice_status'] !== 'signed'
    ) {
        throw new Exception(
            'This invoice can no longer be edited.'
        );
    }
    $invoice['items'] = $invRepo->findItems($id);
    $invoice['totals'] = $invRepo->findTotals($id);
    $invoice['tax_totals'] = $invRepo->findTaxTotals($id);
    $invoice['allowance_charges'] = $invRepo->findAllowances($id);    
    $invoice['allowance_charges'] = $invRepo->findAllowanceCharges($id);
    $invoice['supplier'] = $invSnapshotRepo->findSupplier($id);
    $invoice['customer'] = $invSnapshotRepo->findCustomer($id); 
    if (!$invoice) {
        throw new Exception('Invoice not found.');
    }
    jsonResponse(
        true,
        'Invoice loaded successfully.',
        $invoice
    );
} catch (Exception $e) {
    jsonResponse(
        false,
        $e->getMessage()
    );
}