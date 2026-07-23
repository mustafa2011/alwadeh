<?php

namespace App\Services;

use Exception;

class InvoiceXmlService
{
    public function generate(
        array $invoice,
        string $directory
    ): string {

        $xml = generateInvoiceXml(
            $invoice,
            $directory
        );

        if (!$xml || !file_exists($xml)) {
            throw new Exception(
                'Invoice XML generation failed.'
            );
        }

        return $xml;
    }
}