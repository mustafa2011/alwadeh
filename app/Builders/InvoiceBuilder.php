<?php

namespace App\Builders;
use DateTimeImmutable;
use DateTimeZone;
use App\Services\InvoiceDocumentService;

class InvoiceBuilder
{
    private InvoiceDocumentService $documentService;

    public function __construct()
    {
        $this->documentService = new InvoiceDocumentService();
    }

    public function prepare(
        string $type,
        array $supplier,
        ?string $environment,
        array $chain,
        array $invoiceData,
    ): array {
    
        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('Asia/Riyadh')
        );
    
        $invoice = [
            'uuid' => generateUUID(),
            'id' => $invoiceData['id'] ?? $invoiceData['invoiceNumber'],
            'issueDate' => $invoiceData['issueDate'] ?? $now->format('Y-m-d'),
            'issueTime' => $invoiceData['issueTime'] ?? $now->format('H:i:s'),
            'delivery' => [
                'actualDeliveryDate' => $invoiceData['actualDeliveryDate'] ?? $now->format('Y-m-d'),
            ],            
            'currencyCode' => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'invoiceType' => [
                'invoice' => $type,
                'type' => strtolower(
                    $invoiceData['invoiceType']['type'] ?? 'invoice'
                ),
                'isThirdParty' => false,
                'isNominal' => false,
                'isExport' => false,
                'isSummary' => false,
                'isSelfBilled' => false,
            ],
            'supplier' => $supplier,
            'paymentMeans' => $invoiceData['paymentMeans'] ?? [
                'code' => '10'
            ],
            'environment' => $environment,
        ];
    
        $invoice = array_replace_recursive(
            $invoice,
            $invoiceData
        );
    
        if (
            !isset($invoice['additionalDocuments']) ||
            !is_array($invoice['additionalDocuments'])
        ) {
            $invoice['additionalDocuments'] = [];
        }
    
        $hasICV = false;
        $hasPIH = false;
    
        foreach ($invoice['additionalDocuments'] as &$document) {
    
            if (($document['id'] ?? '') === 'ICV') {
                $document['uuid'] = (string)$chain['icv'];
                $hasICV = true;
            }
    
            if (($document['id'] ?? '') === 'PIH') {
                $document['attachment'] = [
                    'content' => empty($chain['previous_hash'])
                        ? $this->documentService->initialPIH()
                        : $chain['previous_hash']
                ];
                $hasPIH = true;
            }
        }
    
        unset($document);
    
        if (!$hasICV) {
            $invoice['additionalDocuments'][] = [
                'id' => 'ICV',
                'uuid' => (string)$chain['icv'],
            ];
        }
    
        if (!$hasPIH) {
            $invoice['additionalDocuments'][] = [
                'id' => 'PIH',
                'attachment' => [
                    'content' => empty($chain['previous_hash'])
                        ? $this->documentService->initialPIH()
                        : $chain['previous_hash']
                ]
            ];
        }
    
        if (
            !empty($invoiceData['billingRef'])
        ) {
            $invoice['billingReferences'] = [
                [
                    'id' => $invoiceData['billingRef']
                ]
            ];
        }
    
        if (
            $invoice['invoiceType']['type'] === 'credit' ||
            $invoice['invoiceType']['type'] === 'debit'
        ) {
            $invoice['paymentMeans']['note']
                = 'CANCELLATION_OR_TERMINATION';
        }
    
        $invoice['invoice_chain'] = $chain;
    
        return $invoice;
    }

    public function build(
        array $invoice,
        array $totals
    ): array {
        return array_replace_recursive(
            $invoice,
            $totals
        );
    }

    public function buildInvoice(array $supplier, array $options)
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
            $invoice['customer'] = $options['customer'] ?? $this->buildCustomer();
        }
        if (
            !empty($options['hasDelivery']) ||
            $options['type'] === 'standard'
        ) {
            $invoice['delivery'] = $this->buildDelivery($now);
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
    
    public function buildCustomer()
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
    
    
    public function buildDelivery(DateTimeImmutable $date)
    {
        return [
            'actualDeliveryDate' => $date->format('Y-m-d'),
        ];
    } 
        
}