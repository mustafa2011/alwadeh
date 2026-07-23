<?php

namespace App\Services;

class InvoiceCalculationService
{
    
    public function calculate(array $items): array
    {
        $invoiceLines = [];
        $taxGroups = [];
        $lineExtensionTotal = 0;
        $taxTotalAmount = 0;
        $allowanceTotalAmount = 0;
    
        foreach ($items as $index => $item) {
    
            $line = $this->buildInvoiceLine($index, $item);
    
            $invoiceLines[] = $line;
    
            $lineExtensionTotal += $line['lineExtensionAmount'];
            $taxTotalAmount += $line['taxTotal']['taxAmount'];
            $allowanceTotalAmount += $line['allowanceAmount'];
    
            $this->groupTax(
                $taxGroups,
                $line['taxCategory'],
                $line['lineExtensionAmount'],
                $line['taxTotal']['taxAmount']
            );
        }
    
        return [
            'invoiceLines' => $invoiceLines,
            'taxTotal' => $this->buildTaxTotal(
                $taxGroups,
                $taxTotalAmount
            ),
            'legalMonetaryTotal' => $this->buildLegalMonetaryTotal(
                $lineExtensionTotal,
                $taxTotalAmount,
                $allowanceTotalAmount
            ),
            'allowanceCharges' => []
        ];
    }

    private function groupTax(
        array &$groups,
        array $taxCategory,
        float $taxableAmount,
        float $taxAmount
    ): void {
    
        $key = ($taxCategory['id'] ?? 'S') . '_' . ($taxCategory['percent'] ?? 15);
    
        if (!isset($groups[$key])) {
            $groups[$key] = [
                'taxableAmount' => 0,
                'taxAmount' => 0,
                'taxCategory' => $taxCategory
            ];
        }
    
        $groups[$key]['taxableAmount'] += $taxableAmount;
        $groups[$key]['taxAmount'] += $taxAmount;
    } 
    
    private function buildTaxTotal(
        array $groups,
        float $taxAmount
    ): array {
    
        $subTotals = [];
    
        foreach ($groups as $group) {
    
            $subTotals[] = [
                'taxableAmount' => round($group['taxableAmount'], 2),
                'taxAmount' => round($group['taxAmount'], 2),
                'taxCategory' => [
                    'id' => $group['taxCategory']['id'],
                    'percent' => $group['taxCategory']['percent'],
                    'reasonCode' => $group['taxCategory']['reasonCode'] ?? null,
                    'reason' => $group['taxCategory']['reason'] ?? null,
                    'taxScheme' => [
                        'id' => 'VAT'
                    ]
                ]
            ];
        }
    
        return [
            'taxAmount' => round($taxAmount, 2),
            'subTotals' => $subTotals
        ];
    }
    
    private function buildLegalMonetaryTotal(
        float $lineExtension,
        float $tax,
        float $allowance
    ): array {
    
        return [
            'lineExtensionAmount' => round($lineExtension, 2),
            'taxExclusiveAmount' => round($lineExtension, 2),
            'taxInclusiveAmount' => round($lineExtension + $tax, 2),
            'prepaidAmount' => 0,
            'payableAmount' => round($lineExtension + $tax, 2),
            'allowanceTotalAmount' => round($allowance, 2)
        ];
    }
    
    private function buildInvoiceLine(
        int $index,
        array $item
    ): array {
    
        $quantity = round((float)($item['quantity'] ?? 0), 6);
        $unitPrice = round((float)($item['unitPrice'] ?? 0), 6);
    
        $grossAmount = round(
            $quantity * $unitPrice,
            2
        );
    
        $discount = $this->calculateDiscount(
            $grossAmount,
            $item['discount'] ?? []
        );
    
        $lineExtensionAmount = round(
            $grossAmount - $discount,
            2
        );
    
        $taxCategory = $this->normalizeTaxCategory(
            $item['taxCategory'] ?? []
        );
    
        $taxAmount = round(
            $lineExtensionAmount * $taxCategory['percent'] / 100,
            2
        );
    
        return [
            'id' => $index + 1,
            'unitCode' => $item['unitCode'] ?? 'PCE',
            'quantity' => $quantity,
            'lineExtensionAmount' => $lineExtensionAmount,
            'allowanceAmount' => $discount,
            'taxCategory' => $taxCategory,
            'item' => $this->buildItem(
                $item,
                $taxCategory
            ),
            'price' => $this->buildPrice(
                $unitPrice,
                $discount,
                $item
            ),
            'taxTotal' => [
                'taxAmount' => $taxAmount,
                'roundingAmount' => round(
                    $lineExtensionAmount + $taxAmount,
                    2
                )
            ]
        ];
    }   
    
    private function calculateDiscount(
        float $grossAmount,
        array $discount
    ): float {
    
        if (($discount['type'] ?? 'amount') === 'percent') {
    
            $value = round(
                $grossAmount * ((float)($discount['value'] ?? 0) / 100),
                2
            );
    
        } else {
    
            $value = round(
                (float)($discount['value'] ?? 0),
                2
            );
        }
    
        return min($value, $grossAmount);
    }  
    
    private function normalizeTaxCategory(array $taxCategory): array
    {
        $taxCategory = array_merge([
            'id' => 'S',
            'percent' => 15,
            'reasonCode' => null,
            'reason' => null
        ], $taxCategory);
    
        $taxCategory['id'] = strtoupper($taxCategory['id']);
    
        switch ($taxCategory['id']) {
    
            case 'Z':
                $taxCategory['reasonCode'] ??= 'VATEX-SA-32';
                $taxCategory['reason'] ??= 'Zero rated supply';
                break;
    
            case 'E':
                $taxCategory['reasonCode'] ??= 'VATEX-SA-29';
                $taxCategory['reason'] ??= 'VAT exempt supply';
                break;
        }
    
        $taxCategory['percent'] = (float)$taxCategory['percent'];
    
        return $taxCategory;
    }
    
    private function buildItem(
        array $item,
        array $taxCategory
    ): array {
    
        return [
            'name' => $item['name'] ?? '',
            'classifiedTaxCategory' => [
                [
                    'id' => $taxCategory['id'],
                    'percent' => $taxCategory['percent'],
                    'reasonCode' => $taxCategory['reasonCode'],
                    'reason' => $taxCategory['reason'],
                    'taxScheme' => [
                        'id' => 'VAT'
                    ]
                ]
            ]
        ];
    }
    
    private function buildPrice(
        float $unitPrice,
        float $discount,
        array $item
    ): array {
    
        $price = [
            'amount' => $unitPrice,
            'unitCode' => $item['unitCode'] ?? 'PCE',
            'allowanceCharges' => []
        ];
    
        if ($discount > 0) {
    
            $price['allowanceCharges'][] = [
                'isCharge' => false,
                'reason' => $item['discount']['reason'] ?? 'discount',
                'amount' => $discount
            ];
        }
    
        return $price;
    }
    
    
}