<?php

require_once __DIR__.'/../includes/api_bootstrap.php';

use App\Providers\Invoice\DatabaseInvoiceProvider;

header('Content-Type: application/json');

try {

    $provider=new DatabaseInvoiceProvider();

    $invoiceId=(int)($_GET['id']??1);

    $invoice=$provider->getInvoice($invoiceId);    

    echo json_encode([
        'success'=>true,
        'data'=>$invoice
    ],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);


    // $provider = new DatabaseInvoiceProvider($repository);

    // $data = $provider->getInvoice(7);
    
    // echo json_encode(
    //     $data,
    //     JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    // );    

}catch(Throwable $e){

    http_response_code(500);

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage(),
        'file'=>$e->getFile(),
        'line'=>$e->getLine()
    ],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

}