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
        $allowanceStmt = $this->db->prepare("
        SELECT 
            COALESCE(SUM(amount),0)
        FROM invoice_line_allowance_charges ac
        INNER JOIN invoice_lines l
            ON l.id = ac.invoice_line_id
        WHERE l.invoice_id = ?
        AND ac.charge_indicator = 0
    ");
        $allowanceStmt->execute([$invoiceId]);
        $allowanceTotal = (float)$allowanceStmt->fetchColumn();
        
        $chargeStmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(amount),0)
            FROM invoice_line_allowance_charges ac
            INNER JOIN invoice_lines l
                ON l.id = ac.invoice_line_id
            WHERE l.invoice_id = ?
            AND ac.charge_indicator = 1
        ");
        $chargeStmt->execute([$invoiceId]);
        $chargeTotal = (float)$chargeStmt->fetchColumn();

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
            'allowance_total_amount' => $allowanceTotal,
            'charge_total_amount' => $chargeTotal,
            'tax_exclusive_amount' => $legalMonetaryTotal['taxExclusiveAmount'] ?? 0,
            'tax_inclusive_amount' => $legalMonetaryTotal['taxInclusiveAmount'] ?? 0,
            'payable_amount' => $legalMonetaryTotal['payableAmount'] ?? 0,
            'prepaid_amount' => $legalMonetaryTotal['prepaidAmount'] ?? 0,
            'rounding_amount' => $legalMonetaryTotal['roundingAmount'] ?? 0,
        ]);
    }
    public function deleteByInvoiceId(int $invoiceId): void
    {
        $stmt=$this->db->prepare("
            DELETE FROM invoice_totals
            WHERE invoice_id=:invoice_id
        ");
        $stmt->execute([
            'invoice_id'=>$invoiceId
        ]);
    }    
}