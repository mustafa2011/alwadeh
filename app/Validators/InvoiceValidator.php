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
}