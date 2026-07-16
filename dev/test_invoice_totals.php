<?php

require_once __DIR__ . '/../includes/api_bootstrap.php';

header('Content-Type: application/json');

$items = [

    [
        'name' => 'عسل سدر',
        'quantity' => 2,
        'unitPrice' => 100,
        'unitCode' => 'PCE',    
        'discount' => [
            'type' => 'amount',   // amount | percent
            'value' => 20
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
        'discount' => [
            'type' => 'percent', 
            'value' => 5
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
        'discount' => [
            'type' => 'amount', 
            'value' => 0
        ],
        'taxCategory' => [
            'id' => 'Z',
            'percent' => 0
        ]
    ],

];

echo json_encode(
    calculateInvoiceTotals($items),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);