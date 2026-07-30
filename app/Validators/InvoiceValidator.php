<?php

namespace App\Validators;

use Exception;

class InvoiceValidator
{
    public function validateGenerationRequirements(
        array $company,
        array $settings
    ): void {
        if (empty($company)) {
            throw new Exception('Company is not loaded.');
        }

        if (empty($settings)) {
            throw new Exception('Certificate settings not found.');
        }
    }

    public function validateSigningRequirements(
        array $credentials
    ): void {
        if (empty($credentials['certificate'])) {
            throw new Exception('Production certificate not found.');
        }

        if (empty($credentials['secret'])) {
            throw new Exception('Production secret not found.');
        }
    }

    public function getInvoiceType(array $invoiceData): string
    {
        if (
            empty($invoiceData['invoiceType']) ||
            empty($invoiceData['invoiceType']['invoice'])
        ) {
            throw new Exception('Invoice type is required.');
        }

        return strtolower($invoiceData['invoiceType']['invoice']);
    }
    public function validateItem(?array $item): void
    {
        if (!$item) {
            throw new Exception('Item not found.');
        }
    } 
    public function validateUpdateInvoice(?array $invoice): void
    {
        if (!$invoice) {
            throw new Exception(
                'Invoice not found.'
            );
        }
    
        if (($invoice['invoice_status'] ?? null) !== 'signed') {
            throw new Exception(
                'Only signed invoices can be edited.'
            );
        }
    
        if (
            ($invoice['reporting_status'] ?? null) === 'reported' ||
            ($invoice['clearance_status'] ?? null) === 'cleared'
        ) {
            throw new Exception(
                'Submitted invoices cannot be edited.'
            );
        }
    }       
}