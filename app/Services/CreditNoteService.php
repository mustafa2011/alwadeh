<?php
declare(strict_types=1);
namespace App\Services;
class CreditNoteService
{
    public function __construct()
    {
    }
    private function adjustAllowanceCharges(array $allowances,array $chargeIndex,float $ratio,float $lineAmount): array
    {
        foreach($allowances as &$allowance){
            $key=$this->allowanceKey(
                (string)($allowance['reasonCode']??''),
                (bool)($allowance['chargeIndicator']??false)
            );
            if(!isset($chargeIndex[$key])){
                continue;
            }
            $allowance=$this->adjustAllowanceCharge(
                $allowance,
                $chargeIndex[$key],
                $ratio,
                $lineAmount
            );
        }
        unset($allowance);
        return $allowances;
    }
    private function adjustCreditNoteLine(array $item,array $originalLine,array $chargeIndex): array
    {
        $originalQty=(float)($originalLine['quantity']??0);
        $requestedQty=(float)($item['quantity']??0);
        if($originalQty<=0||$requestedQty<=0){
            return $item;
        }
        $ratio=$requestedQty/$originalQty;
        $lineAmount=$requestedQty*(float)($originalLine['unit_price']??0);
        $item['allowanceCharges']=$this->adjustAllowanceCharges(
            $item['allowanceCharges']??[],
            $chargeIndex,
            $ratio,
            $lineAmount
        );
        return $item;
    }
    private function indexAllowanceCharges(array $originalCharges): array
    {
        $index=[];
        foreach($originalCharges as $charge){
            $index[$this->allowanceKey(
                (string)($charge['reason_code']??''),
                (bool)($charge['charge_indicator']??false)
            )]=$charge;
        }
        return $index;
    }
    private function indexOriginalLines(array $originalLines): array
    {
        $index = [];
        foreach ($originalLines as $line) {
            $index[(int)$line['id']] = $line;
        }
        return $index;
    }       
    private function adjustAllowanceCharge(array $allowance,array $originalCharge,float $ratio,float $lineAmount): array
    {
        if(isset($originalCharge['amount'])){
            $allowance['value']=round(
                (float)$originalCharge['amount']*$ratio,
                2
            );
            return $allowance;
        }
        if(isset($originalCharge['multiplier_factor'])){
            $allowance['value']=(float)$originalCharge['multiplier_factor'];
            $allowance['baseAmount']=round($lineAmount,2);
        }
        return $allowance;
    }
    private function allowanceKey(string $reasonCode,bool $chargeIndicator): string
    {
        return $reasonCode.'|'.(int)$chargeIndicator;
    }                       
    public function adjustCreditNoteAllowances(
        array $invoiceData,
        array $originalLines,
        array $originalCharges
    ): array
    {
        $lineIndex = $this->indexOriginalLines($originalLines);
        $chargeIndex = $this->indexAllowanceCharges($originalCharges);        
        foreach ($invoiceData['items'] as &$item) {
            $lineId = (int)($item['originalLineId'] ?? 0);
            if (!isset($lineIndex[$lineId])) {
                continue;
            }
            $item = $this->adjustCreditNoteLine(
                $item,
                $lineIndex[$lineId],
                $chargeIndex
            );
        }
        unset($item);
        return $invoiceData;
    } 
}