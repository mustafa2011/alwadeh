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
            $item = $line['item'] ?? $line;
            $price = $line['price'] ?? [];
            $stmt = $this->db->prepare("
                INSERT INTO invoice_lines (
                    invoice_id,
                    item_id,
                    line_number,
                    item_name,
                    quantity,
                    unit_code,
                    unit_price,
                    line_extension_amount
                ) VALUES (
                    :invoice_id,
                    :item_id,
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
                'item_id' => $item['id'] ?? null,
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
            foreach ($line['allowanceCharges'] ?? [] as $allowance) {
                $taxCategory = $allowance['taxCategory'] ?? [];
                $stmt = $this->db->prepare("
                    INSERT INTO invoice_line_allowance_charges (
                        invoice_line_id,
                        charge_indicator,
                        reason_code,
                        reason,
                        amount,
                        base_amount,
                        multiplier_factor,
                        currency_code,
                        tax_category_id,
                        tax_percent,
                        tax_scheme_id
                    ) VALUES (
                        :invoice_line_id,
                        :charge_indicator,
                        :reason_code,
                        :reason,
                        :amount,
                        :base_amount,
                        :multiplier_factor,
                        :currency_code,
                        :tax_category_id,
                        :tax_percent,
                        :tax_scheme_id
                    )
                ");
                $stmt->execute([
                    'invoice_line_id' => $lineId,
                    'charge_indicator' => !empty($allowance['chargeIndicator']) ? 1 : 0,
                    'reason_code' => $allowance['reasonCode'] ?? null,
                    'reason' => $allowance['reason'] ?? null,
                    'multiplier_factor' => $allowance['multiplierFactorNumeric'] ?? 0,
                    'amount' => $allowance['amount'] ?? 0,
                    'base_amount' => $allowance['baseAmount'] ?? 0,
                    'currency_code' => 'SAR',
                    'tax_category_id' => $taxCategory['id'] ?? 'S',
                    'tax_percent' => $taxCategory['percent'] ?? 15,
                    'tax_scheme_id' => $taxCategory['taxScheme']['id'] ?? 'VAT',
                ]);
            }          
        }
    }
    public function deleteByInvoiceId(int $invoiceId): void
    {
        $stmt=$this->db->prepare("
            DELETE FROM invoice_line_taxes
            WHERE invoice_line_id IN (
                SELECT id FROM invoice_lines WHERE invoice_id=:invoice_id
            )
        ");
        $stmt->execute([
            'invoice_id'=>$invoiceId
        ]);
        $stmt=$this->db->prepare("
            DELETE FROM invoice_lines
            WHERE invoice_id=:invoice_id
        ");
        $stmt->execute([
            'invoice_id'=>$invoiceId
        ]);
    }    
}