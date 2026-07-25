<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Services\InvoiceDraftSubmissionService;

try {

    $input = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($input)) {
        throw new Exception('Invalid data.');
    }

    $invoiceId = (int)($input['invoiceId'] ?? 0);

    if (!$invoiceId) {
        throw new Exception('Invoice id is required.');
    }

    $service = new InvoiceDraftSubmissionService();

    $result = $service->submit($invoiceId);

    echo json_encode([
        'success' => $result['success'],
        'message' => $result['success']
            ? 'Draft invoice submitted successfully.'
            : 'Draft invoice submission failed.',
        'data' => $result
    ],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

}
catch(Throwable $e){

    http_response_code(500);

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage(),
        'file'=>$e->getFile(),
        'line'=>$e->getLine()
    ],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

}