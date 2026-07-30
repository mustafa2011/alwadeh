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

        // $status = strtoupper($submitResult['status'] ?? '');
        $submissionType = $submitResult['submission_type'] ?? null;
        $success = !empty($submitResult['success']);

        $stmt->execute([
            'invoice_id' => $invoiceId,
            'uuid' => $package['uuid'],
            'invoice_hash' => $package['hash'],
            'previous_invoice_hash' => $chain['previous_hash'] ?? null,
            'qr_code' => $package['qr_code'] ?? null,
            'xml_content' => @file_get_contents($package['xml_path']),
            'signed_xml' => $package['signed_xml'],
            'clearance_status' => ($success && $submissionType === 'clearance' )
                ? 'cleared'
                : 'pending',

            'reporting_status' => ($success && $submissionType === 'reporting')
                ? 'reported'
                : 'pending',

            'cleared_at' => ($success && $submissionType === 'clearance')
                ? date('Y-m-d H:i:s')
                : null,
            'zatca_status_code' => $submitResult['statusCode'] ?? null,
            'zatca_response' => json_encode(
                $submitResult,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'submitted_at' => !empty($submitResult['submission_type'])
                ? date('Y-m-d H:i:s')
                : null,            
        ]);
    }

    public function updateAfterSubmission(
        int $invoiceId,
        array $submitResult
    ): void {
    
        $status = strtoupper($submitResult['status'] ?? '');
    
        $stmt = $this->db->prepare("
            UPDATE invoice_zatca
            SET
                clearance_status = :clearance_status,
                reporting_status = :reporting_status,
                zatca_status_code = :status_code,
                zatca_response = :response,
                submitted_at = :submitted_at,
                cleared_at = :cleared_at
            WHERE invoice_id = :invoice_id
        ");
    
        $stmt->execute([
            'clearance_status' => $status === 'CLEARED'
                ? 'cleared'
                : 'pending',
    
            'reporting_status' => $status === 'REPORTED'
                ? 'reported'
                : 'pending',
    
            'status_code' => $submitResult['statusCode'] ?? null,
    
            'response' => json_encode(
                $submitResult,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
    
            'submitted_at' => date('Y-m-d H:i:s'),
    
            'cleared_at' => $status === 'CLEARED'
                ? date('Y-m-d H:i:s')
                : null,
    
            'invoice_id' => $invoiceId
        ]);
    }    
}