<?php
/**
 * Creates Invoice.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

use App\Services\InvoiceService;

try {

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        throw new Exception('Invalid request.');
    }

    $service = new InvoiceService();

    $result = $service->createInvoice($input);

    jsonResponse(
        true,
        'Invoice created successfully.',
        $result
    );

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage()
    );
}