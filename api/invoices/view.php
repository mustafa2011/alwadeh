<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Core\Database;
use App\Repositories\InvoiceRepository;

try {

    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invalid invoice id.');
    }

    $repository = new InvoiceRepository(
        Database::getConnection()
    );

    $invoice = $repository->findById($id);

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