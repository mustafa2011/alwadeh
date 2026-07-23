<?php

namespace App\Services;

use Saleh7\Zatca\InvoiceSigner;
use App\Services\InvoiceCertificateService;

class InvoiceSigningService
{
    private InvoiceCertificateService $certificateService;
    public function __construct()
    {
        $this->certificateService = new InvoiceCertificateService();
    }    
    public function sign(
        string $xmlPath,
        string $invoiceId,
        string $outputDirectory
    ): array {
        $certificate = $this->certificateService->create();
        $signed = InvoiceSigner::signInvoice(
            file_get_contents($xmlPath),
            $certificate
        )->saveXMLFile(
            $invoiceId . '_signed.xml',
            $outputDirectory
        );

        return [
            'signed_xml' => $signed->getXML(),
            'signed_xml_path' => $outputDirectory . DIRECTORY_SEPARATOR . $invoiceId . '_signed.xml',
            'hash' => $signed->getHash(),
            'qr_code' => $signed->getQRCode()
        ];
    }
}