<?php

namespace App\Services;

use TCPDF;

class InvoicePdfService
{
    public function generate(array $data): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);        

        $pdf->SetCreator('ALWADEH ZATCA Portal');
        $pdf->SetAuthor('ALWADEH');
        $pdf->SetTitle('Tax Invoice');
        $pdf->SetMargins(10, 10, 10);
        $pdf->setRTL(true);
        $pdf->setFontSubsetting(true);

        $pdf->AddPage();

        $invoice = $data['invoice'];
        $supplier = $data['supplier'] ?? [];
        $customer = $data['customer'] ?? [];
        $items = $data['items'] ?? [];
        $totals = $data['totals'] ?? [];
        
        $logo = dirname(__DIR__, 2) . '/storage/Companies/logo/logo.png';
        
        if (is_file($logo)) {
        
            $pdf->Image(
                $logo,
                15,
                10,
                35
            );
        
        }
        
        $pdf->SetFont('dejavusans', 'B', 16);
        
        $pdf->Cell(
            0,
            12,
            'ALWADEH ZATCA Portal',
            0,
            1,
            'C'
        );
        
        $pdf->SetFont('dejavusans', '', 9);
        
        $pdf->SetY(32);

        $html = '
        <h3 align="center">فاتورة ضريبية</h3>

        <table border="1" cellpadding="5">
            <tr>
                <td width="50%">
                    رقم الفاتورة: '.htmlspecialchars($invoice['invoice_number'] ?? '').'<br/>
                    تاريخ الإصدار: '.htmlspecialchars($invoice['issue_date'] ?? '').'<br/>
                    UUID: '.htmlspecialchars($invoice['invoice_uuid'] ?? '').'<br/>
                    ICV: '.htmlspecialchars($invoice['icv'] ?? '').'
                </td>
                <td width="50%">
                    الحالة: '.htmlspecialchars($invoice['invoice_status'] ?? '').'<br/>
                    حالة ZATCA: '.htmlspecialchars($invoice['clearance_status'] ?? $invoice['reporting_status'] ?? '').'
                </td>
            </tr>
        </table>

        <br>

        <table border="1" cellpadding="5">
            <tr>

                <td width="50%">
                    <b>المورد</b><br/>
                    '.htmlspecialchars($supplier['party_name'] ?? '').'<br/>
                    الرقم الضريبي: '.htmlspecialchars($supplier['tax']['vat_number'] ?? '').'<br/>
                    '.htmlspecialchars($supplier['address']['street_name'] ?? '').'<br/>
                    '.htmlspecialchars($supplier['address']['building_number'] ?? '').'<br/>
                    '.htmlspecialchars($supplier['address']['city_name'] ?? '').'<br/>
                    الرمز البريدي: '.htmlspecialchars($supplier['address']['postal_zone'] ?? '').'
                </td>

                <td width="50%">
                    <b>العميل</b><br/>
                    '.htmlspecialchars($customer['party_name'] ?? '').'<br/>
                    الرقم الضريبي: '.htmlspecialchars($customer['tax']['vat_number'] ?? '').'<br/>
                    '.htmlspecialchars($customer['address']['street_name'] ?? '').'<br/>
                    '.htmlspecialchars($customer['address']['building_number'] ?? '').'<br/>
                    '.htmlspecialchars($customer['address']['city_name'] ?? '').'<br/>
                    الرمز البريدي: '.htmlspecialchars($customer['address']['postal_zone'] ?? '').'
                </td>

            </tr>
        </table>

        <br>

        <table border="1" cellpadding="4">
            <tr>
                <th width="35%">الصنف</th>
                <th width="15%">الكمية</th>
                <th width="20%">السعر</th>
                <th width="30%">الإجمالي</th>
            </tr>';

        foreach ($items as $item) {

            $html .= '
            <tr>
                <td width="35%">'.htmlspecialchars($item['name'] ?? '').'</td>
                <td width="15%">'.htmlspecialchars($item['quantity'] ?? '').'</td>
                <td width="20%">'.htmlspecialchars($item['unit_price'] ?? $item['price'] ?? '').'</td>
                <td width="30%">'.htmlspecialchars($item['line_extension_amount'] ?? $item['line_total'] ?? '').'</td>
            </tr>';

        }

        $html .= '
        </table>

        <br>

        <table border="1" cellpadding="5">
            <tr>
                <td>
                    الإجمالي قبل الضريبة:
                    '.htmlspecialchars($totals['line_extension_amount'] ?? $totals['subtotal'] ?? '').'
                    <br/>
                    قيمة الضريبة:
                    '.htmlspecialchars($totals['tax_amount'] ?? '').'
                    <br/>
                    الإجمالي شامل الضريبة:
                    '.htmlspecialchars($totals['tax_inclusive_amount'] ?? $totals['total'] ?? '').'
                </td>
            </tr>
        </table>

        <br>

        <table border="1" cellpadding="5">
            <tr>
                <td>
                    UUID:
                    '.htmlspecialchars($invoice['invoice_uuid'] ?? '').'
                    <br/>
                    Invoice Hash:
                    '.htmlspecialchars($invoice['invoice_hash'] ?? '').'
                </td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($html);

        if (!empty($invoice['qr_code'])) {

            $pdf->Ln(8);
        
            $pdf->SetFont('dejavusans', 'B', 10);
        
            $pdf->Cell(
                0,
                8,
                'QR Code - ZATCA',
                0,
                1,
                'C'
            );
        
            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => false
            ];
        
            $pdf->write2DBarcode(
                $invoice['qr_code'],
                'QRCODE,M',
                '',
                '',
                40,
                40,
                $style,
                'N'
            );
        
            $pdf->Ln(42);
        
            $pdf->SetFont('dejavusans', '', 7);
        
            $pdf->Cell(
                0,
                5,
                'UUID: '.$invoice['invoice_uuid'],
                0,
                1,
                'C'
            );
        
            $pdf->Cell(
                0,
                5,
                'Hash: '.$invoice['invoice_hash'],
                0,
                1,
                'C'
            );
        }

        $path = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .$invoice['invoice_number']
            .'.pdf';

        $pdf->Output($path, 'F');

        return $path;
    }
}