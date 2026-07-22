<?php

namespace App\Mappers;

class InvoiceDatabaseMapper
{
    public function map(array $invoice, array $package, array $chain, array $company): array
    {
        return [
            'company_id' => $company['id'],
            'invoice_number' => $invoice['id'],
            'invoice_uuid' => $package['uuid'],
            'invoice_type' => $invoice['invoice_type'] ?? 'invoice',
            'invoice_kind' => $invoice['invoice_type'] === 'standard'
                ? 'standard'
                : 'simplified',
            'issue_date' => date('Y-m-d H:i:s'),
            'issue_time' => date('Y-m-d H:i:s'),
            'icv' => $chain['icv'],
            'previous_invoice_hash' => $chain['previous_hash'] ?? null,
            'invoice_hash' => $package['hash'],
            'xml_file_path' => $package['xml_path'],
            'signed_xml_file_path' => $package['signed_xml_path'],
            'invoice_status' => 'cleared'
        ];
    }
}