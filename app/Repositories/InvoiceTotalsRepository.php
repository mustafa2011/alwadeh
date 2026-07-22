<?php

namespace App\Repositories;
use PDO;

class InvoiceTotalsRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(
        int $invoiceId,
        array $legalMonetaryTotal
    ): void {

        $stmt = $this->db->prepare("
            INSERT INTO invoice_totals (
                invoice_id,
                line_extension_amount,
                allowance_total_amount,
                charge_total_amount,
                tax_exclusive_amount,
                tax_inclusive_amount,
                payable_amount,
                prepaid_amount,
                rounding_amount
            ) VALUES (
                :invoice_id,
                :line_extension_amount,
                :allowance_total_amount,
                :charge_total_amount,
                :tax_exclusive_amount,
                :tax_inclusive_amount,
                :payable_amount,
                :prepaid_amount,
                :rounding_amount
            )
        ");

        $stmt->execute([
            'invoice_id' => $invoiceId,
            'line_extension_amount' => $legalMonetaryTotal['lineExtensionAmount'] ?? 0,
            'allowance_total_amount' => $legalMonetaryTotal['allowanceTotalAmount'] ?? 0,
            'charge_total_amount' => $legalMonetaryTotal['chargeTotalAmount'] ?? 0,
            'tax_exclusive_amount' => $legalMonetaryTotal['taxExclusiveAmount'] ?? 0,
            'tax_inclusive_amount' => $legalMonetaryTotal['taxInclusiveAmount'] ?? 0,
            'payable_amount' => $legalMonetaryTotal['payableAmount'] ?? 0,
            'prepaid_amount' => $legalMonetaryTotal['prepaidAmount'] ?? 0,
            'rounding_amount' => $legalMonetaryTotal['roundingAmount'] ?? 0,
        ]);
    }
}