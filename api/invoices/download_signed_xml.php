<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Core\Database;
use App\Repositories\InvoiceRepository;

try {

    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invoice ID is required.');
    }

    $repository = new InvoiceRepository(
        Database::getConnection()
    );

    $invoice = $repository->findById($id);

    if (!$invoice) {
        throw new Exception('Invoice not found.');
    }

    $path = $invoice['signed_xml_file_path'] ?? null;

    if (!$path || !is_file($path)) {
        throw new Exception('Signed XML file not found.');
    }

    header('Content-Type: application/xml');
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');

    readfile($path);
    exit;

} catch (Throwable $e) {

    http_response_code(400);

    header('Content-Type: text/plain; charset=utf-8');

    echo $e->getMessage();

}