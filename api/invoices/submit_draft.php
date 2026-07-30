<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Services\InvoiceDraftSubmissionService;
use App\Exceptions\ZatcaExceptionHandler;
try {
    $input=json_decode(file_get_contents("php://input"),true);
    if(!is_array($input)){
        throw new Exception('Invalid data.');
    }
    $invoiceId=(int)($input['invoiceId'] ?? 0);
    if(!$invoiceId){
        throw new Exception('Invoice id is required.');
    }
    $service=new InvoiceDraftSubmissionService();
    $result=$service->submit($invoiceId);
    jsonResponse(
        $result['success'],
        $result['success']
            ? 'Signed invoice submitted successfully.'
            : 'Signed invoice submission failed.',
        $result
    );
} catch (\Saleh7\Zatca\Exceptions\ZatcaApiException $e) {
    jsonResponse(
        false,
        $e->getMessage(),
        method_exists($e,'getContext') ? $e->getContext() : []
    );
} catch(Throwable $e){
    jsonResponse(
        false,
        $e->getMessage(),
        [
            'file'=>$e->getFile(),
            'line'=>$e->getLine()
        ]
    );
}