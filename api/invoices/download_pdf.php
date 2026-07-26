<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Core\Database;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceSnapshotRepository;
use App\Services\InvoicePdfService;

try {

    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invoice ID is required.');
    }

    $invRepo = new InvoiceRepository(Database::getConnection());
    $invSnapRepo = new InvoiceSnapshotRepository(Database::getConnection());

    $invoice = $invRepo->findById($id);

    if (!$invoice) {
        throw new Exception('Invoice not found.');
    }

    $data = [
        'invoice' => $invoice,
        'items' => $invRepo->findItems($id),
        'totals' => $invRepo->findTotals($id),
        'taxTotals' => $invRepo->findTaxTotals($id),
        'supplier' => $invSnapRepo->findSupplier($id),
        'customer' => $invSnapRepo->findCustomer($id),
    ];

    $service = new InvoicePdfService();

    $path = $service->generate($data);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));

    if (!file_exists($path)) {
        throw new Exception('PDF file was not created.');
    }
    
    readfile($path);
    
    unlink($path);
    
    exit;

} catch (Throwable $e) {

    http_response_code(400);

    echo $e->getMessage();

}