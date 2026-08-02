<?php
namespace App\Services;
class InvoiceCalculationService
{
    public function calculate(
        array $items,
        array $documentAllowances = []
    ): array {
        $invoiceLines = [];
        $taxGroups = [];
        $lineExtensionTotal = 0;
        foreach ($items as $index => $item) {
            $line = $this->buildInvoiceLine($index, $item);
            $invoiceLines[] = $line;
            $lineExtensionTotal += $line['lineExtensionAmount'];
            $this->groupTax(
                $taxGroups,
                $line['taxCategory'],
                $line['lineExtensionAmount'],
                $line['taxTotal']['taxAmount']
            );
        }
        foreach ($documentAllowances as &$allowance) {
            if (($allowance['mode'] ?? 'amount') === 'percent') {
                $allowance['baseAmount'] = $lineExtensionTotal;
            }
        }
        unset($allowance);
        $documentAllowanceTotal = $this->calculateDocumentAllowanceTotal(
            $documentAllowances
        );
        $documentChargeTotal = $this->calculateDocumentChargeTotal(
            $documentAllowances
        );
        $taxTotalAmount = 0;
        foreach ($taxGroups as &$group) {
            $taxRate = (float)($group['taxCategory']['percent'] ?? 0);
            $ratio = $group['taxableAmount'] / $lineExtensionTotal;
            $allowanceAmount = $documentAllowanceTotal * $ratio;
            $chargeAmount = $documentChargeTotal * $ratio;
            $group['taxableAmount'] = round(
                $group['taxableAmount'] - $allowanceAmount + $chargeAmount,
                2
            );
            $group['taxAmount'] = round(
                $group['taxableAmount'] * $taxRate / 100,
                2
            );
            $taxTotalAmount += $group['taxAmount'];
        }        
        unset($group);       
        $legalMonetaryTotal = $this->buildLegalMonetaryTotal(
            $lineExtensionTotal,
            $taxTotalAmount,
            $documentAllowanceTotal,
            $documentChargeTotal
        );                
        $buildAllowanceCharges = $this->buildAllowanceCharges(
            $documentAllowances
        );      
        return [
            'invoiceLines' => $invoiceLines,
            'taxTotal' => $this->buildTaxTotal(
                $taxGroups,
                $taxTotalAmount
            ),
            'legalMonetaryTotal' => $legalMonetaryTotal,
            'allowanceCharges' => $buildAllowanceCharges
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
                    'percent' => (float)$group['taxCategory']['percent'],
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
        float $allowance,
        float $charge
    ): array {
        $taxExclusive = $lineExtension - $allowance + $charge;
        $taxInclusive = $taxExclusive + $tax;       
        return [
            'lineExtensionAmount' => round($lineExtension, 2),
            'taxExclusiveAmount' => round($taxExclusive, 2),
            'taxInclusiveAmount' => round($taxInclusive, 2),
            'prepaidAmount' => 0,
            'payableAmount' => round($taxInclusive, 2),
            'allowanceTotalAmount' => round($allowance, 2),
            'chargeTotalAmount' => round($charge, 2)
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
        $taxCategory = $this->normalizeTaxCategory(
            $item['taxCategory'] ?? []
        );
        $lineExtensionAmount = $grossAmount;
        $lineExtensionAmount = round(
            $lineExtensionAmount,
            2
        );
        $taxAmount = round(
            $lineExtensionAmount * $taxCategory['percent'] / 100,
            2
        );
        return [
            'id'=>$index+1,
            'unitCode'=>$item['unitCode']??'PCE',
            'quantity'=>$quantity,
            'lineExtensionAmount'=>$lineExtensionAmount,
            'taxCategory'=>$taxCategory,
            'item'=>$this->buildItem(
                $item,
                $taxCategory
            ),
            'price'=>$this->buildPrice(
                $unitPrice,
                []
            ),
            'taxTotal'=>[
                'taxAmount'=>$taxAmount,
                'roundingAmount'=>round(
                    $lineExtensionAmount+$taxAmount,
                    2
                )
            ]
        ];
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
                    'id' => $taxCategory['id'] ?? 'S',
                    'percent' => $taxCategory['percent'] ?? 15,
                    'reasonCode' => $taxCategory['reasonCode'] ?? 95,
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
        array $allowanceCharges
    ): array {
        return [
            'amount'=>$unitPrice,
            'allowanceCharges'=>$allowanceCharges
        ];
    }
    private function calculateDocumentAllowanceTotal(
        array $allowances
    ): float {
        $total = 0;
    
        foreach ($allowances as $allowance) {
            $amount = (float)($allowance['value'] ?? 0);
    
            if (($allowance['mode'] ?? 'amount') === 'percent') {
                $baseAmount = (float)($allowance['baseAmount'] ?? 0);
                $amount = round($baseAmount * $amount / 100, 2);
            }
    
            if (!($allowance['chargeIndicator'] ?? false)) {
                $total += $amount;
            }
        }
    
        return round($total, 2);
    } 
    private function calculateDocumentChargeTotal(
        array $charges
    ): float {
        $total = 0;
    
        foreach ($charges as $charge) {
            if (($charge['chargeIndicator'] ?? false) !== true) {
                continue;
            }
    
            $amount = (float)($charge['value'] ?? 0);
    
            if (($charge['mode'] ?? 'amount') === 'percent') {
                $baseAmount = (float)($charge['baseAmount'] ?? 0);
                $amount = round($baseAmount * $amount / 100, 2);
            }
    
            $total += $amount;
        }
    
        return round($total, 2);
    }        
    private function buildAllowanceCharges(
        array $allowances
    ): array {
        $result = [];
        foreach ($allowances as $allowance) {
            $amount = (float)($allowance['value'] ?? 0);
            if (($allowance['mode'] ?? 'amount') === 'percent') {
                $baseAmount = (float)($allowance['baseAmount'] ?? 0);
                $amount = round($baseAmount * $amount / 100, 2);
            }
            if ($amount <= 0) {
                continue;
            }
            $taxCategory = $allowance['taxCategory'] ?? [
                'id' => 'S',
                'percent' => 15,
                'taxScheme' => [
                    'id' => 'VAT'
                ]
            ];
            $result[] = [
                'chargeIndicator' => (bool)($allowance['chargeIndicator'] ?? false),
                'reasonCode' => $allowance['reasonCode'] ?? null,
                'reason' => trim($allowance['reason'] ?? ''),
                'amount' => $amount,
                'baseAmount' => $allowance['baseAmount'] ?? null,
                'taxCategories' => [
                    $taxCategory
                ],
                'multiplierFactorNumeric' => ($allowance['mode'] ?? 'amount') === 'percent'
                    ? (float)$allowance['value']
                    : null
            ];
        }
        return $result;
    }        
}