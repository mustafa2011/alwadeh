<?php
/**
 * Invoice Helper Functions
 *
 * Handles invoice construction and XML generation.
 *
 * Responsibilities:
 * - Invoice builders
 * - Customer
 * - Delivery
 * - XML generation
 * - Compliance test invoices
 */
use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\GeneratorInvoice;
use Saleh7\Zatca\ZatcaApi;


if (!function_exists('buildCustomer')) {

    /**
     * Build default customer information used for standard invoices.
     *
     * @return array
     */
    function buildCustomer()
    {
        return [
            'registrationName' => 'شركة نماذج فاتورة المحدودة',
            'taxId'            => '399999999800003',

            'address' => [
                'street'         => 'صلاح الدين',
                'buildingNumber' => '1111',
                'subdivision'    => 'Al-Murooj',
                'city'           => 'Riyadh',
                'postalZone'     => '12222',
                'country'        => 'SA',
            ],
        ];
    }
}

if (!function_exists('buildDelivery')) {

    /**
     * Build invoice delivery information.
     *
     * @param DateTimeImmutable $date
     * @return array
     */
    function buildDelivery($date)
    {
        return [
            'actualDeliveryDate' => $date->format('Y-m-d'),
        ];
    }
}

if (!function_exists('buildInvoice')) {

    /**
     * Build invoice data for ZATCA compliance testing.
     *
     * @param array $supplier
     * @param array $options
     * @return array
     */
    function buildInvoice($supplier, $options)
    {
        $uuid = generateUUID();
        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('Asia/Riyadh')
        );
        $invoice = [
            'uuid'            => $uuid,
            'id'              => $options['id'] ?? $options['invoiceNumber'],
            'issueDate'       => $options['issueDate'] ?? $now->format('Y-m-d'),
            'issueTime'       => $options['issueTime'] ?? $now->format('H:i:s'),
            'currencyCode'    => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'invoiceType' => [
                'invoice'      => $options['type'],
                'type'         => $options['subtype'],
                'isThirdParty' => false,
                'isNominal'    => false,
                'isExport'     => false,
                'isSummary'    => false,
                'isSelfBilled' => false,
            ],
            'additionalDocuments' => $options['additionalDocuments'] ?? [
                [
                    'id'   => 'ICV',
                    'uuid' => '1'
                ],
                [
                    'id' => 'PIH',
                    'attachment' => [
                        'content' =>
                        'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ=='
                    ]
                ]
            ],
            'supplier' => $supplier,
            'paymentMeans' => $options['paymentMeans'] ?? [
                'code' => '10'
            ],
            'taxTotal' => $options['taxTotal'] ?? [
                'taxAmount' => 1.50,
                'subTotals' => [
                    [
                        'taxableAmount' => 10,
                        'taxAmount' => 1.50,
                        'taxCategory' => [
                            'percent' => 15,
                            'taxScheme' => [
                                'id' => 'VAT'
                            ]
                        ]
                    ]
                ]
            ],
            'legalMonetaryTotal' => $options['legalMonetaryTotal'] ?? [
                'lineExtensionAmount'  => 10,
                'taxExclusiveAmount'   => 10,
                'taxInclusiveAmount'   => 11.50,
                'prepaidAmount'        => 0,
                'payableAmount'        => 11.50,
                'allowanceTotalAmount' => 0
            ],
            'invoiceLines' => $options['invoiceLines'] ?? [
                [
                    'id' => 1,
                    'unitCode' => 'PCE',
                    'quantity' => 1,
                    'lineExtensionAmount' => 10,
                    'item' => [
                        'name' => 'عسل طبيعي',
                        'classifiedTaxCategory' => [
                            [
                                'percent' => 15,
                                'taxScheme' => [
                                    'id' => 'VAT'
                                ]
                            ]
                        ]
                    ],
                    'price' => [
                        'amount' => 10,
                        'unitCode' => 'UNIT'
                    ],
                    'taxTotal' => [
                        'taxAmount' => 1.50,
                        'roundingAmount' => 11.50
                    ]
                ]
            ]
        ];
        if (!empty($options['billingRef'])) {
            $invoice['billingReferences'] = [
                [
                    'id' => $options['billingRef']
                ]
            ];
        }
        if (
            $options['subtype'] === 'credit' ||
            $options['subtype'] === 'debit'
        ) {
            $invoice['paymentMeans']['note']
                = 'CANCELLATION_OR_TERMINATION';
        }
        if (
            !empty($options['hasCustomer']) ||
            $options['type'] === 'standard'
        ) {
            $invoice['customer'] = $options['customer'] ?? buildCustomer();
        }
        if (
            !empty($options['hasDelivery']) ||
            $options['type'] === 'standard'
        ) {
            $invoice['delivery'] = buildDelivery($now);
        }
        if (isset($options['invoice_chain'])) {
            $invoice['invoice_chain'] = $options['invoice_chain'];
        }       
        if (isset($options['environment'])) {
            $invoice['environment'] = $options['environment'];
        }       
        if (isset($options['invoice_state'])) {
            $invoice['invoice_state'] = $options['invoice_state'];
        }
        if (isset($options['invoice_type'])) {
            $invoice['invoice_type'] = $options['invoice_type'];
        }
        if (!empty($options['allowanceCharges'])) {
            $invoice['allowanceCharges'] = $options['allowanceCharges'];
        }        
        return $invoice;
    }
}

if (!function_exists('getComplianceInvoices')) {

    /**
     * Build all compliance test invoices required by ZATCA.
     *
     * @param array $supplier
     * @return array
     */
    function getComplianceInvoices($supplier)
    {
        return [

            [
                'label' => '1/6 Standard Invoice',

                'data' => buildInvoice($supplier, [

                    'id' => 'STD00001',

                    'type' => 'standard',

                    'subtype' => 'invoice',

                    'hasCustomer' => true,

                    'hasDelivery' => true,

                ]),

            ],

            [
                'label' => '2/6 Standard Credit Note',

                'data' => buildInvoice($supplier, [

                    'id' => 'STD00002',

                    'type' => 'standard',

                    'subtype' => 'credit',

                    'hasCustomer' => true,

                    'hasDelivery' => true,

                    'billingRef' => 'STD00002',

                ]),

            ],

            [
                'label' => '3/6 Standard Debit Note',

                'data' => buildInvoice($supplier, [

                    'id' => 'STD00003',

                    'type' => 'standard',

                    'subtype' => 'debit',

                    'hasCustomer' => true,

                    'hasDelivery' => true,

                    'billingRef' => 'STD00003',

                ]),

            ],

            [
                'label' => '4/6 Simplified Invoice',

                'data' => buildInvoice($supplier, [

                    'id' => 'SIM00004',

                    'type' => 'simplified',

                    'subtype' => 'invoice',

                ]),

            ],

            [
                'label' => '5/6 Simplified Credit Note',

                'data' => buildInvoice($supplier, [

                    'id' => 'SIM00005',

                    'type' => 'simplified',

                    'subtype' => 'credit',

                    'billingRef' => 'SIM00005',

                ]),

            ],

            [
                'label' => '6/6 Simplified Debit Note',

                'data' => buildInvoice($supplier, [

                    'id' => 'SIM00006',

                    'type' => 'simplified',

                    'subtype' => 'debit',

                    'billingRef' => 'SIM00006',

                ]),

            ],

        ];
    }
}

if (!function_exists('generateInvoiceXml')) {

    /**
     * Generate invoice XML file.
     *
     * @param array  $invoiceData
     * @param string $outputDirectory
     *
     * @return string
     */
    function generateInvoiceXml(
        $invoiceData,
        $outputDirectory
    ) {

        $invoice = (new InvoiceMapper())
            ->mapToInvoice($invoiceData);

        GeneratorInvoice::invoice($invoice)
            ->saveXMLFile(
                $invoiceData['id'] . '.xml',
                $outputDirectory
            );

        return $outputDirectory
            . '/'
            . $invoiceData['id']
            . '.xml';
    }
}

/**
 * Save invoice chain state.
 */
function saveInvoiceState(
    string $stateFile,
    array $state
): void {
    $default = [
        'last_icv' => 0,
        'last_invoice_hash' => '',
        'last_uuid' => '',
        'last_invoice_number' => '',
        'last_invoice_type' => '',
        'last_submission_type' => '',
        'updated_at' => ''
    ];

    $state = array_merge($default, $state);
    $state['updated_at'] = gmdate('c');

    file_put_contents(
        $stateFile,
        json_encode(
            $state,
            JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE
        )
    );
}

/**
 * Submit a signed invoice to ZATCA.
 * @param object $api
 */
function submitInvoice(
    $api,
    array $credentials,
    string $signedXml,
    string $invoiceHash,
    string $uuid,
    bool $isSimplified
): array {

    if ($isSimplified) {
        $result = $api->submitReportingInvoice(
            $credentials['certificate'],
            $credentials['secret'],
            $signedXml,
            $invoiceHash,
            $uuid
        );
        $submissionStatus = $result->getReportingStatus();
        $success =
            $result->isReported()
            || in_array($result->getStatusCode(), [200, 202], true);
    } else {
        $result = $api->submitClearanceInvoice(
            $credentials['certificate'],
            $credentials['secret'],
            $signedXml,
            $invoiceHash,
            $uuid
        );
        $submissionStatus = $result->getClearanceStatus();
        $success =
            $result->isCleared()
            || in_array($result->getStatusCode(), [200, 202], true);

    }

    return [
        'success' => $success,    
        'statusCode' => $result->getStatusCode(),   
        'status' => $submissionStatus,    
        'submission_type' => $isSimplified ? 'reporting' : 'clearance',    
        'warnings' => $result->getWarningMessages(),    
        'errors' => $result->getErrorMessages(),    
        'cleared_xml' => (!$isSimplified && $result->isCleared())
            ? $result->getDecodedClearedInvoice()
            : null,    
        'response' => $result->toArray()    
    ];
}


function getInitialPIH(): string
{
    return 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';
}

/**
 * Calculate invoice totals and build ZATCA invoice lines.
 *
 * @param array $items
 * @return array
 */
function calculateInvoiceTotals(array $items): array
{
    $invoiceLines = [];
    $taxGroups = [];
    $lineExtensionTotal = 0;
    $taxTotalAmount = 0;
    $allowanceTotalAmount = 0;
    foreach ($items as $index => $item) {
        $quantity = round((float)($item['quantity'] ?? 0), 6);
        $unitPrice = round((float)($item['unitPrice'] ?? 0), 6);
        $grossAmount = round($quantity * $unitPrice, 2);
        $discount = $item['discount'] ?? [
            'type' => 'amount',
            'value' => 0
        ];
        $discountValue = 0;
        if (($discount['type'] ?? 'amount') === 'percent') {
            $discountValue = round(
                $grossAmount * ((float)($discount['value'] ?? 0) / 100),
                2
            );
        } else {
            $discountValue = round(
                (float)($discount['value'] ?? 0),
                2
            );
        }
        $discountValue = min($discountValue, $grossAmount);
        $lineExtensionAmount = round(
            $grossAmount - $discountValue,
            2
        );
        $allowanceTotalAmount += $discountValue;
        $taxCategory = $item['taxCategory'] ?? [
            'id' => 'S',
            'percent' => 15,
            'reasonCode' => null,
            'reason' => null
        ];
        $taxCategoryId = strtoupper($taxCategory['id'] ?? 'S');

        if ($taxCategoryId === 'Z') {
            $taxCategory['reasonCode'] ??= 'VATEX-SA-32';
            $taxCategory['reason'] ??= 'Zero rated supply';
        }
        elseif ($taxCategoryId === 'E') {
            $taxCategory['reasonCode'] ??= 'VATEX-SA-29';
            $taxCategory['reason'] ??= 'VAT exempt supply';
        }        
        $taxPercent = (float)($taxCategory['percent'] ?? 15);
        $taxAmount = round(
            $lineExtensionAmount * $taxPercent / 100,
            2
        );
        $roundingAmount = round(
            $lineExtensionAmount + $taxAmount,
            2
        );
        $lineExtensionTotal += $lineExtensionAmount;
        $taxTotalAmount += $taxAmount;
        $key = ($taxCategory['id'] ?? 'S')
            . '_'
            . $taxPercent;
        if (!isset($taxGroups[$key])) {
            $taxGroups[$key] = [
                'taxableAmount' => 0,
                'taxAmount' => 0,
                'taxCategory' => $taxCategory
            ];
        }
        $taxGroups[$key]['taxableAmount'] += $lineExtensionAmount;
        $taxGroups[$key]['taxAmount'] += $taxAmount;
        $invoiceLines[] = [
            'id' => $index + 1,
            'unitCode' => $item['unitCode'] ?? 'PCE',
            'quantity' => $quantity,
            'lineExtensionAmount' => $lineExtensionAmount,
            'item' => [
                'name' => $item['name'] ?? '',
                'classifiedTaxCategory' => [
                    [
                        'id' => $taxCategory['id'] ?? 'S',
                        'percent' => $taxPercent,
                        'reasonCode' => $taxCategory['reasonCode'] ?? null,
                        'reason' => $taxCategory['reason'] ?? null,
                        'taxScheme' => [
                            'id' => 'VAT'
                        ]
                    ]
                ]
            ],
            'price' => [
                'amount' => $unitPrice,
                'unitCode' => $item['unitCode'] ?? 'PCE',
                'allowanceCharges' => $discountValue > 0 ? [
                    [
                        'isCharge' => false,
                        'reason' => $item['discount']['reason'] ?? 'discount',
                        'amount' => $discountValue,
                    ]
                ] : []
            ],            
            'taxTotal' => [
                'taxAmount' => $taxAmount,
                'roundingAmount' => $roundingAmount
            ]
        ];
    }
    $taxSubTotals = [];
    foreach ($taxGroups as $group) {
        $taxSubTotals[] = [
            'taxableAmount' => round($group['taxableAmount'], 2),
            'taxAmount' => round($group['taxAmount'], 2),
            'taxCategory' => [
                'id' => $group['taxCategory']['id'] ?? 'S',
                'percent' => $group['taxCategory']['percent'] ?? 15,
                'reasonCode' => $group['taxCategory']['reasonCode'] ?? null,
                'reason' => $group['taxCategory']['reason'] ?? null,
                'taxScheme' => [
                    'id' => 'VAT'
                ]
            ]
        ];
    }
    $taxTotal = [
        'taxAmount' => round($taxTotalAmount, 2),
        'subTotals' => $taxSubTotals
    ];
    $legalMonetaryTotal = [
        'lineExtensionAmount' => round($lineExtensionTotal, 2),
        'taxExclusiveAmount' => round($lineExtensionTotal, 2),
        'taxInclusiveAmount' => round($lineExtensionTotal + $taxTotalAmount, 2),
        'prepaidAmount' => 0,
        'payableAmount' => round($lineExtensionTotal + $taxTotalAmount, 2),
        'allowanceTotalAmount' => 0
    ];    
    return [
        'invoiceLines' => $invoiceLines,
        'taxTotal' => $taxTotal,
        'legalMonetaryTotal' => $legalMonetaryTotal,
        'allowanceCharges' => []
    ];    
}