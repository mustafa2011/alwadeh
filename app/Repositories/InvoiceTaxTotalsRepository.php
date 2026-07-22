<?php

namespace App\Repositories;
use PDO;

class InvoiceTaxTotalsRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(
        int $invoiceId,
        array $taxTotal
    ): void {

        foreach ($taxTotal['subTotals'] ?? [] as $subTotal) {

            $taxCategory = $subTotal['taxCategory'] ?? [];
            $taxScheme = $taxCategory['taxScheme'] ?? [];

            $stmt = $this->db->prepare("
                INSERT INTO invoice_tax_totals (
                    invoice_id,
                    tax_amount,
                    taxable_amount,
                    tax_category_id,
                    tax_percent,
                    tax_scheme_id
                ) VALUES (
                    :invoice_id,
                    :tax_amount,
                    :taxable_amount,
                    :tax_category_id,
                    :tax_percent,
                    :tax_scheme_id
                )
            ");

            $stmt->execute([
                'invoice_id' => $invoiceId,
                'tax_amount' => $subTotal['taxAmount'] ?? 0,
                'taxable_amount' => $subTotal['taxableAmount'] ?? 0,
                'tax_category_id' => $taxCategory['id'] ?? null,
                'tax_percent' => $taxCategory['percent'] ?? 0,
                'tax_scheme_id' => $taxScheme['id'] ?? 'VAT',
            ]);
        }
    }
}