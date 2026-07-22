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
            'invoice_number' => $invoice['invoice_number'],
            'invoice_uuid' => $invoice['invoice_uuid'],
            'invoice_type' => $invoice['invoice_type'],
            'invoice_kind' => $invoice['invoice_kind'],
            'issue_date' => $invoice['issue_date'],
            'issue_time' => $invoice['issue_time'],
            'supply_date' => $invoice['issue_time'],
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
}