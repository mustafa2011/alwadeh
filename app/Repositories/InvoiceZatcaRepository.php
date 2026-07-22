<?php

namespace App\Repositories;
use PDO;

class InvoiceZatcaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(
        int $invoiceId,
        array $package,
        array $chain,
        array $submitResult
    ): void {

        $stmt = $this->db->prepare("
            INSERT INTO invoice_zatca (
                invoice_id,
                uuid,
                invoice_hash,
                previous_invoice_hash,
                qr_code,
                xml_content,
                signed_xml,
                clearance_status,
                reporting_status,
                zatca_status_code,
                zatca_response,
                submitted_at,
                cleared_at
            ) VALUES (
                :invoice_id,
                :uuid,
                :invoice_hash,
                :previous_invoice_hash,
                :qr_code,
                :xml_content,
                :signed_xml,
                :clearance_status,
                :reporting_status,
                :zatca_status_code,
                :zatca_response,
                :submitted_at,
                :cleared_at
            )
        ");

        $status = strtoupper($submitResult['status'] ?? '');

        $stmt->execute([
            'invoice_id' => $invoiceId,
            'uuid' => $package['uuid'],
            'invoice_hash' => $package['hash'],
            'previous_invoice_hash' => $chain['previous_hash'] ?? null,
            'qr_code' => $package['qr_code'] ?? null,
            'xml_content' => @file_get_contents($package['xml_path']),
            'signed_xml' => $package['signed_xml'],
            'clearance_status' => $status === 'CLEARED' ? 'cleared' : 'pending',
            'reporting_status' => $status === 'REPORTED' ? 'reported' : 'pending',
            'zatca_status_code' => $submitResult['statusCode'] ?? null,
            'zatca_response' => json_encode(
                $submitResult,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'submitted_at' => date('Y-m-d H:i:s'),
            'cleared_at' => $status === 'CLEARED'
                ? date('Y-m-d H:i:s')
                : null,
        ]);
    }
}