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
}