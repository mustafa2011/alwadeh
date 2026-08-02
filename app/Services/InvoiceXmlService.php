<?php
namespace App\Services;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\GeneratorInvoice;
use Exception;
class InvoiceXmlService
{
    private InvoiceSigningService $invoiceSigningService;
    public function __construct()
    {
        $this->invoiceSigningService = new InvoiceSigningService();
    }
    public function generate(
        array $invoice,
        string $directory
    ): string {     
        $xml = $this->generateInvoiceXml(
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
    public function buildPackage(
        array $invoice,
        string $directory
    ): array {
        $xmlPath = $this->generate(
            $invoice,
            $directory
        );
        $signed = $this->invoiceSigningService->sign(
            $xmlPath,
            $invoice['id'],
            $directory
        );
        return [
            'invoice' => $invoice,
            'xml_path' => $xmlPath,
            'signed_xml' => $signed['signed_xml'],
            'signed_xml_path' => $signed['signed_xml_path'],
            'hash' => $signed['hash'],
            'qr_code' => $signed['qr_code'],
            'invoice_id' => $invoice['id'],
            'uuid' => $invoice['uuid']
        ];
    }
    public function generateInvoiceXml(
        array $invoiceData,
        string $outputDirectory
    ): string {
        if (!empty($invoiceData['allowanceCharges'])) {
            foreach ($invoiceData['allowanceCharges'] as &$charge) {
                $charge['isCharge'] = $charge['chargeIndicator'] ?? false;
            }
            unset($charge);
        }        
        $invoice = (new InvoiceMapper())->mapToInvoice($invoiceData);                
        GeneratorInvoice::invoice($invoice)
            ->saveXMLFile(
                $invoiceData['id'] . '.xml',
                $outputDirectory
            );
        return $outputDirectory
            . DIRECTORY_SEPARATOR
            . $invoiceData['id']
            . '.xml';
    }
}