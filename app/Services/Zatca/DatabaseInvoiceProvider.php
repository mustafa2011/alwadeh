<?php

namespace App\Services\Zatca;
use PDO;

class DatabaseInvoiceProvider
{
    public function __construct(private PDO $db) {}

    public function getInvoiceData(int $invoiceId): array
    {
        return [
            'uuid' => $this->getInvoiceUUID($invoiceId),
            'id' => $this->getInvoiceNumber($invoiceId),
            'issueDate' => $this->getIssueDate($invoiceId),
            'issueTime' => $this->getIssueTime($invoiceId),
            'invoiceType' => $this->getInvoiceType($invoiceId),
            'currencyCode' => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'supplier' => $this->getSupplier($invoiceId),
            'customer' => $this->getCustomer($invoiceId),
            'delivery' => $this->getDelivery($invoiceId),
            'paymentMeans' => $this->getPaymentMeans($invoiceId),
            'taxTotal' => $this->getTaxTotal($invoiceId),
            'legalMonetaryTotal' => $this->getTotals($invoiceId),
            'invoiceLines' => $this->getInvoiceLines($invoiceId),
            'signature' => [],
        ];
    }

    private function getSupplier(int $invoiceId): array
    {
        $sql = "
            SELECT
    
                c.id AS company_id,
                c.company_name,
                c.registration_name,
    
                cp.party_identification_id,
                cp.party_identification_scheme,
    
                ca.street_name,
                ca.building_number,
                ca.plot_identification,
                ca.city_name,
                ca.postal_zone,
                ca.country_code,
    
                cts.company_id,
                cts.tax_scheme_id,
                cts.tax_scheme_name,
    
                cle.registration_name AS legal_registration_name,
                cle.company_id AS legal_company_id
    
    
            FROM invoices i
    
            INNER JOIN companies c
                ON c.id = i.company_id
    
    
            LEFT JOIN company_party cp
                ON cp.company_id = c.id
    
    
            LEFT JOIN company_address ca
                ON ca.company_id = c.id
    
    
            LEFT JOIN company_tax_scheme cts
                ON cts.company_id = c.id
    
    
            LEFT JOIN company_legal_entity cle
                ON cle.company_id = c.id
    
    
            WHERE i.id = :invoice_id
    
            LIMIT 1
        ";
    
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$row) {
            throw new \Exception(
                "Supplier data not found for invoice {$invoiceId}"
            );
        }
    
        return [
            'partyIdentification' => [
                'id' => $row['party_identification_id'],
                'schemeID' => $row['party_identification_scheme']    
            ],        
            'partyName' => [    
                'name' => $row['company_name']    
            ],        
            'postalAddress' => [    
                'streetName' => $row['street_name'],    
                'buildingNumber' => $row['building_number'],    
                'plotIdentification' => $row['plot_identification'],    
                'cityName' => $row['city_name'],    
                'postalZone' => $row['postal_zone'],
                'country' => [
                    'identificationCode' => $row['country_code']
                ]    
            ],        
            'partyTaxScheme' => [
                'companyID' => $row['tax_scheme_id'],
                'taxScheme' => [
                    'id' => $row['tax_scheme_name']
                ]
            ],
            'legalEntity' => [    
                'registrationName' => $row['legal_registration_name']    
            ]  
        ];
    }
    
    private function getCustomer(int $invoiceId): array{
        $sql = "
            SELECT
                c.id AS customer_id,
                c.customer_name,
                c.registration_name,
                cp.party_identification_id,
                cp.party_identification_scheme,
                ca.street_name,
                ca.building_number,
                ca.plot_identification,
                ca.city_name,
                ca.postal_zone,
                ca.country_code,
                cts.tax_scheme_id,
                cts.tax_scheme_name,
                cle.registration_name AS legal_registration_name
            FROM invoices i
            INNER JOIN customers c
                ON c.id = i.customer_id
            LEFT JOIN customer_party cp
                ON cp.customer_id = c.id
            LEFT JOIN customer_address ca
                ON ca.customer_id = c.id
            LEFT JOIN customer_tax_scheme cts
                ON cts.customer_id = c.id
            LEFT JOIN customer_legal_entity cle
                ON cle.customer_id = c.id
            WHERE i.id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['invoice_id' => $invoiceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \Exception(
                "Customer data not found for invoice {$invoiceId}"
            );
        }
        return [
            'partyIdentification' => [
                'id' => $row['party_identification_id'],
                'schemeID' => $row['party_identification_scheme']
            ],
            'partyName' => [
                'name' => $row['customer_name']
            ],
            'postalAddress' => [
                'streetName' => $row['street_name'],
                'buildingNumber' => $row['building_number'],
                'plotIdentification' => $row['plot_identification'],
                'cityName' => $row['city_name'],
                'postalZone' => $row['postal_zone'],
                'country' => [
                    'identificationCode' => $row['country_code']
                ]
            ],
            'partyTaxScheme' => [
                'companyID' => $row['tax_scheme_id'],
                'taxScheme' => [
                    'id' => $row['tax_scheme_name']
                ]
            ],
            'legalEntity' => [
                'registrationName' => $row['legal_registration_name']
            ]
        ];
    }

    private function getInvoiceLines(int $invoiceId): array 
    {
        $sql = "
            SELECT
                il.id AS line_id,
                il.line_number,
                il.quantity,
                il.unit_code,
                il.unit_price,
                il.line_extension_amount,
                il.item_name,
                il.item_description,
                il.tax_percent,
                il.tax_category_code,
                il.tax_amount,
                il.item_classification_code
            FROM invoice_lines il
            WHERE il.invoice_id = :invoice_id
            ORDER BY il.line_number ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            throw new \Exception(
                "Invoice lines not found for invoice {$invoiceId}"
            );
        }
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = [
                'id' => (string)$row['line_number'],
                'invoicedQuantity' => [
                    'value' => $row['quantity'],
                    'unitCode' => $row['unit_code']
                ],
                'lineExtensionAmount' => [
                    'value' => $row['line_extension_amount'],
                    'currencyID' => 'SAR'
                ],
                'item' => [
                    'name' => $row['item_name'],
                    'description' => $row['item_description'],
                    'classifiedTaxCategory' => [
                        'id' => $row['tax_category_code'],
                        'percent' => $row['tax_percent'],
                        'taxScheme' => [
                            'id' => 'VAT'
                        ]
                    ],
                    'commodityClassification' => [
                        'itemClassificationCode' => $row['item_classification_code']
                    ]
                ],
                'price' => [
                    'priceAmount' => [
                        'value' => $row['unit_price'],
                        'currencyID' => 'SAR'
                    ]
                ],
                'taxAmount' => [
                    'value' => $row['tax_amount'],
                    'currencyID' => 'SAR'
                ]
            ];
        }
        return $lines;
    }

    private function getTaxTotal(int $invoiceId): array
    {
        $sql = "
            SELECT
                it.tax_amount,
                it.taxable_amount,
                it.tax_percent,
                it.tax_category_code,
                it.tax_scheme
            FROM invoice_taxes it
            WHERE it.invoice_id = :invoice_id
            ORDER BY it.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return [
                'taxAmount' => [
                    'value' => 0,
                    'currencyID' => 'SAR'
                ],
                'taxSubtotal' => []
            ];
        }
        $taxSubTotals = [];
        $totalTax = 0;
        foreach ($rows as $row) {
            $totalTax += $row['tax_amount'];
            $taxSubTotals[] = [
                'taxableAmount' => [
                    'value' => $row['taxable_amount'],
                    'currencyID' => 'SAR'
                ],
                'taxAmount' => [
                    'value' => $row['tax_amount'],
                    'currencyID' => 'SAR'
                ],
                'percent' => $row['tax_percent'],
                'taxCategory' => [
                    'id' => $row['tax_category_code'],
                    'percent' => $row['tax_percent'],
                    'taxScheme' => [
                        'id' => $row['tax_scheme'] ?? 'VAT'
                    ]
                ]
            ];
        }
        return [
            'taxAmount' => [
                'value' => $totalTax,
                'currencyID' => 'SAR'
            ],
            'taxSubtotal' => $taxSubTotals
        ];
    }

    private function getTotals(int $invoiceId): array
    {
        $sql = "
            SELECT
                line_extension_amount,
                tax_exclusive_amount,
                tax_amount,
                tax_inclusive_amount,
                allowance_total_amount,
                charge_total_amount,
                payable_amount
            FROM invoice_totals
            WHERE invoice_id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \Exception(
                "Invoice totals not found for invoice {$invoiceId}"
            );
        }
        return [
            'lineExtensionAmount' => [
                'value' => $row['line_extension_amount'],
                'currencyID' => 'SAR'
            ],
            'taxExclusiveAmount' => [
                'value' => $row['tax_exclusive_amount'],
                'currencyID' => 'SAR'
            ],
            'taxInclusiveAmount' => [
                'value' => $row['tax_inclusive_amount'],
                'currencyID' => 'SAR'
            ],
            'allowanceTotalAmount' => [
                'value' => $row['allowance_total_amount'],
                'currencyID' => 'SAR'
            ],
            'chargeTotalAmount' => [
                'value' => $row['charge_total_amount'],
                'currencyID' => 'SAR'
            ],
            'payableAmount' => [
                'value' => $row['payable_amount'],
                'currencyID' => 'SAR'
            ]
        ];
    }

    private function getInvoiceType(int $invoiceId): array
    {
        $sql = "
            SELECT
                invoice_type_code,
                invoice_type_name
            FROM invoices
            WHERE id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \Exception(
                "Invoice type not found for invoice {$invoiceId}"
            );
        }
        return [
            'invoiceTypeCode' => $row['invoice_type_code'],
            'name' => $row['invoice_type_name']
        ];
    }

    private function getDelivery(int $invoiceId): array
    {
        $sql = "
            SELECT
                actual_delivery_date,
                latest_delivery_date
            FROM invoice_delivery
            WHERE invoice_id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [];
        }
        return [
            'actualDeliveryDate' => $row['actual_delivery_date'],
            'latestDeliveryDate' => $row['latest_delivery_date']
        ];
    }

    private function getPaymentMeans(int $invoiceId): array
    {
        $sql = "
            SELECT
                payment_means_code,
                instruction_note,
                payment_id,
                payment_account_id,
                payment_account_name
            FROM invoice_payment_means
            WHERE invoice_id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [];
        }
        return [
            'paymentMeansCode' => $row['payment_means_code'],
            'instructionNote' => $row['instruction_note'],
            'paymentID' => $row['payment_id'],
            'payeeFinancialAccount' => [
                'id' => $row['payment_account_id'],
                'name' => $row['payment_account_name']
            ]
        ];
    }

    private function getInvoiceUUID(int $invoiceId): string
    {
        $sql = "
            SELECT uuid
            FROM invoices
            WHERE id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        $uuid = $stmt->fetchColumn();

        if (!$uuid) {
            throw new \Exception(
                "Invoice UUID not found for invoice {$invoiceId}"
            );
        }

        return $uuid;
    }

    private function getInvoiceNumber(int $invoiceId): string
    {
        $sql = "
            SELECT invoice_number
            FROM invoices
            WHERE id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);

        return (string)$stmt->fetchColumn();
    }

    private function getIssueDate(int $invoiceId): string
    {
        $sql = "
            SELECT issue_date
            FROM invoices
            WHERE id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);

        return $stmt->fetchColumn();
    }

    private function getIssueTime(int $invoiceId): string
    {
        $sql = "
            SELECT issue_time
            FROM invoices
            WHERE id = :invoice_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);

        return $stmt->fetchColumn() ?: '';
    }

}