<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceZatcaRepository;

class InvoiceDraftSubmissionService
{
    private InvoiceRepository $invoiceRepository;
    private InvoiceSubmissionService $submissionService;
    private InvoiceChainService $chainService;
    private InvoiceZatcaRepository $invoiceZatcaRepository;

    public function __construct()
    {
        $this->invoiceRepository = new InvoiceRepository(
            \App\Core\Database::getConnection()
        );
        $this->submissionService = new InvoiceSubmissionService();
        $this->chainService = new InvoiceChainService();
        $this->invoiceZatcaRepository = new InvoiceZatcaRepository(
            \App\Core\Database::getConnection()
        );        
    }

    public function submit(int $invoiceId): array
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        
        if (!$invoice) {
            throw new \Exception('Invoice not found.');
        }
    
        if (($invoice['invoice_status'] ?? null) !== 'signed') {
            throw new \Exception(
                'Only signed invoices can be submitted.'
            );
        }

        $package = [
            'db_invoice_id' => $invoiceId,
            'invoice_id' => $invoice['invoice_number'],
            'uuid' => $invoice['invoice_uuid'],
            'hash' => $invoice['invoice_hash'],
            'signed_xml_path' => $invoice['signed_xml_file_path'],
            'signed_xml' => file_get_contents(
                $invoice['signed_xml_file_path']
            ),
            'invoice' => [
                'invoiceType' => [
                    'invoice' => $invoice['invoice_kind']
                ]
            ]
        ];       
        $api = $this->chainService->api();
        
        $result = $this->submissionService->submit(
            $api,
            $package
        );
        
        if ($result['success']) {

            $status =
                ($result['submission_type'] ?? null) === 'clearance'
                    ? 'cleared'
                    : 'reported';

            $this->invoiceRepository->updateStatusAfterSubmission(
                $invoiceId,
                $status
            );
            $this->invoiceZatcaRepository->updateAfterSubmission(
                $invoiceId,
                $result
            );            
        }
        else {

            $this->invoiceRepository->updateStatusAfterSubmission(
                $invoiceId,
                'rejected'
            );
        
            $this->invoiceZatcaRepository->updateAfterSubmission(
                $invoiceId,
                $result
            );
        
        }
        return $result;
        
    }
}