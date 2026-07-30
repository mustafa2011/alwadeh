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
    $result=$service->updateInvoice(
        (int)($invoiceData['invoiceId']??0),
        $invoiceData,
        $invoiceData['submit']??false
    );    
    echo json_encode([
        'success'=>$result['success'],
        'message'=>$result['message'],
        'data'=>$result
    ],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);    
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