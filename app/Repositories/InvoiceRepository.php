<?php

namespace App\Repositories;
use PDO;

class InvoiceRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $invoice): int
    {
        $sql = "
        INSERT INTO invoices (
            company_id,
            customer_id,
            invoice_number,
            invoice_uuid,
            invoice_type,
            invoice_kind,
            billing_reference,
            original_invoice_id,            
            issue_date,
            issue_time,
            supply_date,
            currency_code,
            document_currency_code,
            tax_currency_code,
            icv,
            previous_invoice_hash,
            invoice_hash,
            xml_file_path,
            signed_xml_file_path,
            invoice_status,
            qr_code,
            created_by
        ) VALUES (
            :company_id,
            :customer_id,
            :invoice_number,
            :invoice_uuid,
            :invoice_type,
            :invoice_kind,
            :billing_reference,
            :original_invoice_id,            
            :issue_date,
            :issue_time,
            :supply_date,
            :currency_code,
            :document_currency_code,
            :tax_currency_code,
            :icv,
            :previous_invoice_hash,
            :invoice_hash,
            :xml_file_path,
            :signed_xml_file_path,
            :invoice_status,
            :qr_code,
            :created_by
        )";

        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            'company_id' => $invoice['company_id'],
            'customer_id' =>  $invoice['customer_id'] ?? null,
            'invoice_number' => $invoice['invoice_number'],
            'invoice_uuid' => $invoice['invoice_uuid'],
            'invoice_type' => $invoice['invoice_type'],
            'invoice_kind' => $invoice['invoice_kind'],
            'billing_reference' => $invoice['billing_reference'] ?? null,
            'original_invoice_id' => $invoice['original_invoice_id'] ?? null,                        
            'issue_date' => $invoice['issue_date'],
            'issue_time' => $invoice['issue_time'],
            'supply_date' => $invoice['issue_date'],            
            'currency_code' => $invoice['currency_code'],
            'document_currency_code' => $invoice['document_currency_code'],
            'tax_currency_code' => $invoice['tax_currency_code'],
            'icv' => $invoice['icv'],
            'previous_invoice_hash' => $invoice['previous_invoice_hash'],
            'invoice_hash' => $invoice['invoice_hash'],
            'xml_file_path' => $invoice['xml_file_path'],
            'signed_xml_file_path' => $invoice['signed_xml_file_path'],
            'invoice_status' => $invoice['invoice_status'],
            'qr_code' => $invoice['qr_code'],
            'created_by' => $invoice['created_by']
        ]);

        return (int) $this->db->lastInsertId();
    }
    public function update(int $id, array $invoice): void
    {
        $sql = "
        UPDATE invoices SET
            invoice_hash=:invoice_hash,
            xml_file_path=:xml_file_path,
            signed_xml_file_path=:signed_xml_file_path,
            qr_code=:qr_code
        WHERE id=:id
        ";
        $stmt=$this->db->prepare($sql);
        $stmt->execute([
            'id'=>$id,
            'invoice_hash'=>$invoice['invoice_hash'],
            'xml_file_path'=>$invoice['xml_file_path'],
            'signed_xml_file_path'=>$invoice['signed_xml_file_path'],
            'qr_code'=>$invoice['qr_code'] ?? null
        ]);
    }
    public function findLastIssuedInvoice(int $companyId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                icv,
                invoice_hash
            FROM invoices
            WHERE company_id = ?
            ORDER BY icv DESC
            LIMIT 1
        ");

        $stmt->execute([$companyId]);

        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        return $invoice ?: null;
    }   
    
    public function findDraft(int $invoiceId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                company_id,
                invoice_number,
                invoice_uuid,
                invoice_kind,
                invoice_hash,
                xml_file_path,
                signed_xml_file_path,
                invoice_status
            FROM invoices
            WHERE id = ?
            AND invoice_status = 'draft'
            LIMIT 1
        ");
    
        $stmt->execute([$invoiceId]);
    
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $invoice ?: null;
    }
    
    public function updateStatusAfterSubmission(
        int $invoiceId,
        string $status
    ): void
    {
        $stmt = $this->db->prepare("
            UPDATE invoices
            SET
                invoice_status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
    
        $stmt->execute([
            'status' => $status,
            'id' => $invoiceId
        ]);
    }  

    public function updateZatcaXmlPath(
        int $invoiceId,
        string $path
    ): void {
    
        $stmt = $this->db->prepare("
            UPDATE invoices
            SET zatca_xml_file_path = ?
            WHERE id = ?
        ");
    
        $stmt->execute([
            $path,
            $invoiceId
        ]);
    
    }
        
    public function findById(int $invoiceId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                i.*,
                z.clearance_status,
                z.reporting_status,
                z.zatca_status_code,
                z.submitted_at,
                z.cleared_at
            FROM invoices i
            LEFT JOIN invoice_zatca z
                ON z.invoice_id = i.id
            WHERE i.id = ?
            LIMIT 1
        ");
    
        $stmt->execute([$invoiceId]);
    
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $invoice ?: null;
    }    

    public function findItems(int $invoiceId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                l.*,
                t.tax_percent,
                t.tax_amount,
                (l.line_extension_amount+t.tax_amount) AS payable_amount,
                COALESCE(ac.discount_amount,0) AS discount_amount,
                COALESCE(ac.discount_percentage,0) AS discount_percentage,
                ac.charge_indicator,
                ac.discount_reason
            FROM invoice_lines l
            LEFT JOIN invoice_line_taxes t
                ON t.invoice_line_id=l.id
            LEFT JOIN (
                SELECT
                    invoice_line_id,
                    SUM(amount) AS discount_amount,
                    MAX(multiplier_factor) AS discount_percentage,
                    MAX(charge_indicator) AS charge_indicator,
                    MAX(reason) AS discount_reason
                FROM invoice_line_allowance_charges
                WHERE charge_indicator=0
                GROUP BY invoice_line_id
            ) ac
                ON ac.invoice_line_id=l.id
            WHERE l.invoice_id=?
            ORDER BY l.line_number
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findTotals(int $invoiceId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM invoice_totals
            WHERE invoice_id = ?
            LIMIT 1
        ");
    
        $stmt->execute([$invoiceId]);
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $row ?: null;
    }
    
    public function findTaxTotals(int $invoiceId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM invoice_tax_totals
            WHERE invoice_id = ?
            ORDER BY id
        ");
    
        $stmt->execute([$invoiceId]);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }  
    public function createAllowances(
        int $invoiceId,
        array $allowances
    ): void {
        if (empty($allowances)) {
            return;
        }
        $stmt = $this->db->prepare("
            INSERT INTO invoice_allowance_charges (
                invoice_id,
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
                :invoice_id,
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
    
        foreach ($allowances as $allowance) {
            $taxCategory = $allowance['taxCategories'][0] ?? [];
    
            $stmt->execute([
                'invoice_id' => $invoiceId,
                'charge_indicator' => !empty($allowance['chargeIndicator']) ? 1 : 0,
                'reason_code' => $allowance['reasonCode'] ?? null,
                'reason' => $allowance['reason'] ?? null,
                'amount' => $allowance['amount'] ?? 0,
                'base_amount' => $allowance['baseAmount'] ?? 0,
                'multiplier_factor' => $allowance['multiplierFactorNumeric'] ?? 0,
                'currency_code' => 'SAR',
                'tax_category_id' => $taxCategory['id'] ?? 'S',
                'tax_percent' => $taxCategory['percent'] ?? 15,
                'tax_scheme_id' => $taxCategory['taxScheme']['id'] ?? 'VAT'
            ]);
        }
    }
    public function deleteAllowancesByInvoiceId(int $invoiceId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM invoice_allowance_charges
            WHERE invoice_id = ?
        ");
        $stmt->execute([$invoiceId]);
    } 
    public function nextInvoiceNumber(int $companyId): string
    {
        $stmt=$this->db->prepare("
            SELECT MAX(id) 
            FROM invoices 
            WHERE company_id=:company_id
        ");
        $stmt->execute([
            'company_id'=>$companyId
        ]);
        $last=(int)$stmt->fetchColumn();
        return 'INV'.str_pad($last+1,5,'0',STR_PAD_LEFT);
    }   
    public function findAllowanceCharges(int $invoiceId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM invoice_allowance_charges
            WHERE invoice_id = :invoice_id AND amount > 0 
            ORDER BY id
        ");
        $stmt->execute([
            'invoice_id' => $invoiceId
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } 
    public function findReportedInvoices(int $companyId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                i.id,
                i.invoice_number,
                t.payable_amount,
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='credit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0) AS credited_amount,
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='debit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0) AS debited_amount,
                t.payable_amount
                -
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='credit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0)
                +
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='debit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0) AS remaining_amount
            FROM invoices i
            INNER JOIN invoice_totals t
                ON t.invoice_id=i.id
            WHERE i.company_id=?
            AND i.invoice_kind='simplified'
            AND i.invoice_status='reported'
            AND i.invoice_type='invoice'
            ORDER BY i.id DESC
        ");
        $stmt->execute([$companyId]);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as &$invoice){
            $invoice['lines']=$this->getInvoiceLinesRemaining(
                (int)$invoice['id']
            );
        }
        
        return $data;        
    }       
    public function findClearedInvoices(int $companyId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                i.id,
                i.invoice_number,
                t.payable_amount,
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='credit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0) AS credited_amount,
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='debit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0) AS debited_amount,
                t.payable_amount
                -
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='credit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0)
                +
                COALESCE((
                    SELECT SUM(nt.payable_amount)
                    FROM invoices n
                    INNER JOIN invoice_totals nt
                        ON nt.invoice_id=n.id
                    WHERE n.original_invoice_id=i.id
                    AND n.invoice_type='debit_note'
                    AND n.invoice_status IN ('reported','cleared')
                ),0) AS remaining_amount
            FROM invoices i
            INNER JOIN invoice_totals t
                ON t.invoice_id=i.id
            WHERE i.company_id=?
            AND i.invoice_kind='standard'
            AND i.invoice_status='cleared'
            AND i.invoice_type='invoice'
            ORDER BY i.id DESC
        ");
        $stmt->execute([$companyId]);
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as &$invoice){
            $invoice['lines']=$this->getInvoiceLinesRemaining(
                (int)$invoice['id']
            );
        }
        
        return $data;    
    }   
    public function sumNotesTotal(int $invoiceId): float
    {
        $stmt=$this->db->prepare("
            SELECT COALESCE(SUM(t.payable_amount),0)
            FROM invoices n
            INNER JOIN invoice_totals t
                ON t.invoice_id=n.id
            WHERE n.original_invoice_id=?
            AND n.invoice_type IN ('credit_note','debit_note')
        ");
    
        $stmt->execute([$invoiceId]);
    
        return (float)$stmt->fetchColumn();
    }  
    private function getInvoiceLinesRemaining(int $invoiceId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                l.item_name,
                l.quantity AS original_quantity,
                COALESCE(SUM(nl.quantity),0) AS credited_quantity,
                l.quantity-COALESCE(SUM(nl.quantity),0) AS remaining_quantity
            FROM invoice_lines l
            LEFT JOIN invoices n
                ON n.original_invoice_id=l.invoice_id
                AND n.invoice_type='credit_note'
            LEFT JOIN invoice_lines nl
                ON nl.invoice_id=n.id
                AND nl.item_name=l.item_name
            WHERE l.invoice_id=?
            GROUP BY
                l.id,
                l.item_name,
                l.quantity
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    public function findRemainingLines(int $invoiceId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                l.item_id,
                l.item_name,
                l.quantity AS original_quantity,
                l.quantity -
                COALESCE((
                    SELECT SUM(cl.quantity)
                    FROM invoices n
                    INNER JOIN invoice_lines cl
                        ON cl.invoice_id=n.id
                    WHERE n.original_invoice_id=l.invoice_id
                    AND n.invoice_type='credit_note'
                    AND n.invoice_status IN ('reported','cleared')
                    AND cl.item_name=l.item_name
                ),0) AS remaining_quantity,
                l.unit_price,
                COALESCE(t.tax_percent,15) AS tax_percent,
                l.unit_code,
                (
                    (
                        l.quantity -
                        COALESCE((
                            SELECT SUM(cl.quantity)
                            FROM invoices n
                            INNER JOIN invoice_lines cl
                                ON cl.invoice_id=n.id
                            WHERE n.original_invoice_id=l.invoice_id
                            AND n.invoice_type='credit_note'
                            AND n.invoice_status IN ('reported','cleared')
                            AND cl.item_name=l.item_name
                        ),0)
                    ) * l.unit_price
                ) AS remaining_line_amount
            FROM invoice_lines l
            LEFT JOIN invoice_line_taxes t
                ON t.invoice_line_id=l.id
            WHERE l.invoice_id=?
            ORDER BY l.id
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findRemainingData(int $invoiceId): ?array
    {
        $stmt=$this->db->prepare("
            SELECT
                i.id,
                t.payable_amount,
                COALESCE(SUM(
                    CASE 
                        WHEN n.invoice_type='credit_note'
                        THEN nt.payable_amount
                        ELSE 0
                    END
                ),0) AS credited_amount,
                COALESCE(SUM(
                    CASE 
                        WHEN n.invoice_type='debit_note'
                        THEN nt.payable_amount
                        ELSE 0
                    END
                ),0) AS debited_amount,
                ROUND(
                    t.payable_amount
                    -
                    COALESCE(SUM(
                        CASE 
                            WHEN n.invoice_type='credit_note'
                            THEN nt.payable_amount
                            ELSE 0
                        END
                    ),0)
                    +
                    COALESCE(SUM(
                        CASE 
                            WHEN n.invoice_type='debit_note'
                            THEN nt.payable_amount
                            ELSE 0
                        END
                    ),0),
                    2
                ) AS remaining_amount
            FROM invoices i
            INNER JOIN invoice_totals t
                ON t.invoice_id=i.id
            LEFT JOIN invoices n
                ON n.original_invoice_id=i.id
            LEFT JOIN invoice_totals nt
                ON nt.invoice_id=n.id
            WHERE i.id=?
            GROUP BY i.id,t.payable_amount
        ");
        $stmt->execute([$invoiceId]);
        $data=$stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        $remaining=(float)$data['remaining_amount'];
        if(abs($remaining)<0.05){
            $data['remaining_amount']=0;
        }
        $data['financial_remaining_amount']=$data['remaining_amount'];
        return $data;
    }
    public function findLastSuccessfulNote(int $invoiceId): ?int
    {
        $stmt=$this->db->prepare("
            SELECT id
            FROM invoices
            WHERE original_invoice_id=?
            AND invoice_type IN ('credit_note','debit_note')
            AND invoice_status IN ('reported','cleared')
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$invoiceId]);
        $id=$stmt->fetchColumn();
        return $id ? (int)$id : null;
    }                           
    public function findPreviousCreditQuantities(int $invoiceId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                cl.item_id,
                cl.item_name,
                COALESCE(SUM(cl.quantity),0) AS returned_quantity
            FROM invoices n
            INNER JOIN invoice_lines cl
                ON cl.invoice_id=n.id
            WHERE n.original_invoice_id=?
            AND n.invoice_type='credit_note'
            AND n.invoice_status IN ('reported','cleared')
            GROUP BY cl.item_id,cl.item_name
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }       
    public function findCreditNotes(int $invoiceId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                n.id,
                l.original_line_id,
                l.quantity
            FROM invoices n
            INNER JOIN invoice_lines l
                ON l.invoice_id=n.id
            WHERE n.original_invoice_id=?
            AND n.invoice_type='credit_note'
            AND n.invoice_status IN ('reported','cleared')
            ORDER BY n.id
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    
    public function findPreviousCredits(int $invoiceId): array
    {
        $stmt=$this->db->prepare("
            SELECT
                n.id,
                n.original_invoice_id
            FROM invoices n
            WHERE n.original_invoice_id=?
            AND n.invoice_type='credit_note'
            AND n.invoice_status IN ('reported','cleared')
            ORDER BY n.id
        ");
        $stmt->execute([$invoiceId]);
        $credits=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($credits as &$credit){
            $credit['items']=$this->findItems((int)$credit['id']);
        }
        unset($credit);
        return $credits;
    }    
}