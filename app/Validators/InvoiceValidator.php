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
            empty($invoiceData['invoiceType']['invoiceKind'])
        ) {
            throw new Exception('Invoice kind is required.');
        }
        return strtolower($invoiceData['invoiceType']['invoiceKind']);
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
    public function validateOriginalInvoice(
        ?array $originalInvoice,
        string $invoiceType
    ): void {
        if (!in_array($invoiceType, ['credit', 'debit'], true)) {
            return;
        }
        if (!$originalInvoice) {
            throw new Exception('Original invoice not found.');
        }
        if (
            ($originalInvoice['invoice_kind'] ?? null) !== 'standard'
            &&
            ($originalInvoice['invoice_kind'] ?? null) !== 'simplified'
        ) {
            throw new Exception('Invalid original invoice.');
        }
    }   
    public function validateCreditDebitNote(
        array $invoiceData,
        array $originalLines,
        array $previousCredits
    ): void
    {
        $type=$invoiceData['invoiceType']['invoiceType']??'invoice';
        if($type!=='credit'){
            return;
        }
        $remaining=$this->buildRemainingQuantities(
            $originalLines,
            $previousCredits
        );
        $this->validateCreditNoteRemainingQuantity(
            $invoiceData['items']??[],
            $remaining
        );
    }                   
    private function buildRemainingQuantities(array $originalLines,array $previousCredits): array
    {
        $remaining=[];
        foreach($originalLines as $line){
            $remaining[(int)$line['item_id']]=(float)$line['quantity'];
        }
        foreach($previousCredits as $credit){
            foreach($credit['items']??[] as $item){
                $itemId=(int)($item['item_id']??0);
                if(isset($remaining[$itemId])){
                    $remaining[$itemId]-=(float)($item['quantity']??0);
                }
            }
        }
        return $remaining;
    }   
    public function validateCreditNoteRemainingQuantity(array $items,array $remaining): void
    {
        foreach($items as $item){
            $itemId=(int)($item['itemId']??$item['item_id']??0);
            $qty=(float)($item['quantity']??0);
            if(!isset($remaining[$itemId])){
                throw new \Exception('Original item not found.');
            }
            if($qty>$remaining[$itemId]){
                throw new \Exception(
                    sprintf(
                        'Remaining quantity is %.3f but requested %.3f.',
                        $remaining[$itemId],
                        $qty
                    )
                );
            }
        }
    }   
}