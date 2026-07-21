<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Services\InvoiceService;

try {


    /*
    |--------------------------------------------------------------------------
    | Read JSON Request
    |--------------------------------------------------------------------------
    */

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



    /*
    |--------------------------------------------------------------------------
    | 1. Save Invoice Draft
    |--------------------------------------------------------------------------
    */
    $service = new InvoiceService();

    $result = $service->createInvoice($invoiceData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Invoice created successfully.',
        'data' => $result
    ]);
    
    exit;

    /*
    |--------------------------------------------------------------------------
    | 2. Submit To ZATCA Service
    |--------------------------------------------------------------------------
    */


    $service = new InvoiceService();



    $zatcaResult =
        $service->issueInvoice(
            $invoiceData
        );



    /*
    |--------------------------------------------------------------------------
    | 3. Update Database
    |--------------------------------------------------------------------------
    */


    $repository->updateAfterZatca(

        $invoiceId,

        $zatcaResult

    );




    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */


    echo json_encode([

        'success'=>true,

        'message'=>
            'Invoice created successfully.',

        'data'=>[

            'invoice_id'=>$invoiceId,

            'zatca'=>$zatcaResult

        ]

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