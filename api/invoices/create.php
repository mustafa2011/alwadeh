<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Services\InvoiceService;
try {
    $input = file_get_contents("php://input");
    $invoiceData = json_decode(
        $input,
        true
    );
    if (!is_array($invoiceData)) {
        throw new Exception(
            "Invalid invoice data."
        );
    }
    $service = new InvoiceService();
    if (!empty($invoiceData['invoiceId'])) {
        $result = $service->updateInvoice(
            (int)$invoiceData['invoiceId'],
            $invoiceData,
            $invoiceData['submit'] ?? true
        );
    } else {
        $documentType = strtolower(
            $invoiceData['invoiceType']['invoiceType'] ?? 'invoice'
        );
        if (
            in_array(
                $documentType,
                ['credit','debit'],
                true
            )
        ) {
            $result = $service->createNote(
                $invoiceData
            );
        } else {
            $result = $service->issueInvoice(
                $invoiceData,
                $invoiceData['submit'] ?? true
            );
        }
    }
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'],
        'data' => $result['data']
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