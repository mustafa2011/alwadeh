<?php
/**
 * Invoice Helper Functions
 *
 */

function calculateInvoiceTotals(array $items): array
{
    $invoiceLines = [];
    $taxGroups = [];
    $lineExtensionTotal = 0;
    $taxTotalAmount = 0;
    $allowanceTotalAmount = 0;
    foreach ($items as $index => $item) {
        $quantity = round((float)($item['quantity'] ?? 0), 6);
        $unitPrice = round((float)($item['unitPrice'] ?? 0), 6);
        $grossAmount = round($quantity * $unitPrice, 2);
        $discount = $item['discount'] ?? [
            'type' => 'amount',
            'value' => 0
        ];
        $discountValue = 0;
        if (($discount['type'] ?? 'amount') === 'percent') {
            $discountValue = round(
                $grossAmount * ((float)($discount['value'] ?? 0) / 100),
                2
            );
        } else {
            $discountValue = round(
                (float)($discount['value'] ?? 0),
                2
            );
        }
        $discountValue = min($discountValue, $grossAmount);
        $lineExtensionAmount = round(
            $grossAmount - $discountValue,
            2
        );
        $allowanceTotalAmount += $discountValue;
        $taxCategory = $item['taxCategory'] ?? [
            'id' => 'S',
            'percent' => 15,
            'reasonCode' => null,
            'reason' => null
        ];
        $taxCategoryId = strtoupper($taxCategory['id'] ?? 'S');

        if ($taxCategoryId === 'Z') {
            $taxCategory['reasonCode'] ??= 'VATEX-SA-32';
            $taxCategory['reason'] ??= 'Zero rated supply';
        }
        elseif ($taxCategoryId === 'E') {
            $taxCategory['reasonCode'] ??= 'VATEX-SA-29';
            $taxCategory['reason'] ??= 'VAT exempt supply';
        }        
        $taxPercent = (float)($taxCategory['percent'] ?? 15);
        $taxAmount = round(
            $lineExtensionAmount * $taxPercent / 100,
            2
        );
        $roundingAmount = round(
            $lineExtensionAmount + $taxAmount,
            2
        );
        $lineExtensionTotal += $lineExtensionAmount;
        $taxTotalAmount += $taxAmount;
        $key = ($taxCategory['id'] ?? 'S')
            . '_'
            . $taxPercent;
        if (!isset($taxGroups[$key])) {
            $taxGroups[$key] = [
                'taxableAmount' => 0,
                'taxAmount' => 0,
                'taxCategory' => $taxCategory
            ];
        }
        $taxGroups[$key]['taxableAmount'] += $lineExtensionAmount;
        $taxGroups[$key]['taxAmount'] += $taxAmount;
        $invoiceLines[] = [
            'id' => $index + 1,
            'unitCode' => $item['unitCode'] ?? 'PCE',
            'quantity' => $quantity,
            'lineExtensionAmount' => $lineExtensionAmount,
            'item' => [
                'name' => $item['name'] ?? '',
                'classifiedTaxCategory' => [
                    [
                        'id' => $taxCategory['id'] ?? 'S',
                        'percent' => $taxPercent,
                        'reasonCode' => $taxCategory['reasonCode'] ?? null,
                        'reason' => $taxCategory['reason'] ?? null,
                        'taxScheme' => [
                            'id' => 'VAT'
                        ]
                    ]
                ]
            ],
            'price' => [
                'amount' => $unitPrice,
                'unitCode' => $item['unitCode'] ?? 'PCE',
                'allowanceCharges' => $discountValue > 0 ? [
                    [
                        'isCharge' => false,
                        'reason' => $item['discount']['reason'] ?? 'discount',
                        'amount' => $discountValue,
                    ]
                ] : []
            ],            
            'taxTotal' => [
                'taxAmount' => $taxAmount,
                'roundingAmount' => $roundingAmount
            ]
        ];
    }
    $taxSubTotals = [];
    foreach ($taxGroups as $group) {
        $taxSubTotals[] = [
            'taxableAmount' => round($group['taxableAmount'], 2),
            'taxAmount' => round($group['taxAmount'], 2),
            'taxCategory' => [
                'id' => $group['taxCategory']['id'] ?? 'S',
                'percent' => $group['taxCategory']['percent'] ?? 15,
                'reasonCode' => $group['taxCategory']['reasonCode'] ?? null,
                'reason' => $group['taxCategory']['reason'] ?? null,
                'taxScheme' => [
                    'id' => 'VAT'
                ]
            ]
        ];
    }
    $taxTotal = [
        'taxAmount' => round($taxTotalAmount, 2),
        'subTotals' => $taxSubTotals
    ];
    $legalMonetaryTotal = [
        'lineExtensionAmount' => round($lineExtensionTotal, 2),
        'taxExclusiveAmount' => round($lineExtensionTotal, 2),
        'taxInclusiveAmount' => round($lineExtensionTotal + $taxTotalAmount, 2),
        'prepaidAmount' => 0,
        'payableAmount' => round($lineExtensionTotal + $taxTotalAmount, 2),
        'allowanceTotalAmount' => 0
    ];    
    return [
        'invoiceLines' => $invoiceLines,
        'taxTotal' => $taxTotal,
        'legalMonetaryTotal' => $legalMonetaryTotal,
        'allowanceCharges' => []
    ];    
}