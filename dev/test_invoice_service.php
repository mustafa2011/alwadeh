<?php
require_once __DIR__ . '/../includes/api_bootstrap.php';

use App\Services\InvoiceService;

header('Content-Type: application/json');

try {

    $service = new InvoiceService();

    // Simplified invoice
    $invoiceSIM = [
        'id' => 'SIM00001',

        'invoiceType' => [
            'invoice' => 'simplified',
            'type' => 'invoice'
        ],

        'items' => [

            [
                'name' => 'عسل سدر',
                'quantity' => 2,
                'unitPrice' => 100,
                'unitCode' => 'PCE',

                // خصم على مستوى السطر
                'allowanceCharges' => [
                    [
                        'isCharge' => false,
                        'reason' => 'discount',
                        'amount' => 20
                    ]
                ],

                'taxCategory' => [
                    'id' => 'S',
                    'percent' => 15
                ]
            ],


            [
                'name' => 'شاي أخضر',
                'quantity' => 3,
                'unitPrice' => 25,
                'unitCode' => 'PCE',

                // 5% من (3 × 25 = 75) = 3.75
                'allowanceCharges' => [
                    [
                        'isCharge' => false,
                        'reason' => 'discount',
                        'amount' => 3.75
                    ]
                ],

                'taxCategory' => [
                    'id' => 'S',
                    'percent' => 15
                ]
            ],


            [
                'name' => 'كتاب',
                'quantity' => 1,
                'unitPrice' => 80,
                'unitCode' => 'PCE',

                // بدون خصم
                'allowanceCharges' => [],

                'taxCategory' => [
                    'id' => 'Z',
                    'percent' => 0
                ]
            ],

        ]
    ];

    // Standard invoice
    $invoiceSTD = [

        'id' => 'STD00001',
    
        'invoiceType' => [
            'invoice' => 'standard',
            'type' => 'invoice'
        ],
    
        'buyer' => [
            'name' => 'شركة العميل للتجارة',
            'vatNumber' => '300000000000003',
            'country' => 'SA',
            'city' => 'Riyadh',
            'street' => 'King Fahd Road',
            'buildingNumber' => '1234',
            'postalCode' => '12345'
        ],
    
        'items' => [
    
            [
                'name' => 'لابتوب Dell',
                'quantity' => 2,
                'unitPrice' => 2500,
                'unitCode' => 'PCE',
    
                // خصم على مستوى السطر
                'allowanceCharges' => [
                    [
                        'isCharge' => false,
                        'reason' => 'discount',
                        'amount' => 200
                    ]
                ],
    
                'taxCategory' => [
                    'id' => 'S',
                    'percent' => 15
                ]
            ],
    
            [
                'name' => 'ماوس لاسلكي',
                'quantity' => 5,
                'unitPrice' => 100,
                'unitCode' => 'PCE',
    
                'allowanceCharges' => [],
    
                'taxCategory' => [
                    'id' => 'S',
                    'percent' => 15
                ]
            ]
    
        ]
    ];    


    $result = $service->issueInvoice($invoiceSIM);
    // $result = $service->issueInvoice($invoiceSTD);

    echo json_encode(
        $result,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );


} catch (Throwable $e) {

    $result = [
        'success' => false,
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ];

    if ($e instanceof \Saleh7\Zatca\Exceptions\ZatcaApiException) {
        $result['context'] = $e->getContext();
    }

    echo json_encode(
        $result,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
}