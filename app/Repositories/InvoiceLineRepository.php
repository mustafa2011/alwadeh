<?php

namespace App\Repositories;
use PDO;

class InvoiceLineRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(int $invoiceId, array $lines): void
    {
        foreach ($lines as $index => $line) {

            $item = $line['item'] ?? [];
            $price = $line['price'] ?? [];

            $stmt = $this->db->prepare("
                INSERT INTO invoice_lines (
                    invoice_id,
                    line_number,
                    item_name,
                    quantity,
                    unit_code,
                    unit_price,
                    line_extension_amount
                ) VALUES (
                    :invoice_id,
                    :line_number,
                    :item_name,
                    :quantity,
                    :unit_code,
                    :unit_price,
                    :line_extension_amount
                )
            ");

            $stmt->execute([
                'invoice_id' => $invoiceId,
                'line_number' => $index + 1,
                'item_name' => $item['name'] ?? '',
                'quantity' => $line['quantity'] ?? 0,
                'unit_code' => $line['unitCode'] ?? 'PCE',
                'unit_price' => $price['amount'] ?? 0,
                'line_extension_amount' => $line['lineExtensionAmount'] ?? 0,
            ]);

            $lineId = (int) $this->db->lastInsertId();

            foreach ($item['classifiedTaxCategory'] ?? [] as $taxCategory) {

                $stmt = $this->db->prepare("
                    INSERT INTO invoice_line_taxes (
                        invoice_line_id,
                        taxable_amount,
                        tax_amount,
                        tax_category_id,
                        tax_percent,
                        tax_scheme_id
                    ) VALUES (
                        :invoice_line_id,
                        :taxable_amount,
                        :tax_amount,
                        :tax_category_id,
                        :tax_percent,
                        :tax_scheme_id
                    )
                ");

                $stmt->execute([
                    'invoice_line_id' => $lineId,
                    'taxable_amount' => $line['lineExtensionAmount'] ?? 0,
                    'tax_amount' => $line['taxTotal']['taxAmount'] ?? 0,
                    'tax_category_id' => $taxCategory['id'] ?? null,
                    'tax_percent' => $taxCategory['percent'] ?? 0,
                    'tax_scheme_id' => $taxCategory['taxScheme']['id'] ?? 'VAT',
                ]);
            }
        }
    }
}