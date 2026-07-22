<?php

namespace App\Builders;

class InvoiceBuilder
{
    public function prepare(
        string $type,
        array $supplier,
        ?string $environment,
        array $invoiceState,
        array $chain,
        array $invoiceData
    ): array {
        $invoice = array_replace_recursive(
            [
                'invoice_type' => $type,
                'supplier' => $supplier,
                'environment' => $environment,
                'invoice_state' => $invoiceState,
            ],
            $invoiceData
        );

        $invoice['type'] = $type;
        $invoice['subtype'] = strtolower(
            $invoiceData['invoiceType']['type'] ?? 'invoice'
        );

        if (
            !isset($invoice['additionalDocuments']) ||
            !is_array($invoice['additionalDocuments'])
        ) {
            $invoice['additionalDocuments'] = [];
        }

        $hasICV = false;
        $hasPIH = false;

        foreach ($invoice['additionalDocuments'] as &$document) {
            if (($document['id'] ?? '') === 'ICV') {
                $document['uuid'] = (string) $chain['icv'];
                $hasICV = true;
            }

            if (($document['id'] ?? '') === 'PIH') {
                $document['attachment'] = [
                    'content' => empty($chain['previous_hash'])
                        ? getInitialPIH()
                        : $chain['previous_hash']
                ];
                $hasPIH = true;
            }
        }

        unset($document);

        if (!$hasICV) {
            $invoice['additionalDocuments'][] = [
                'id' => 'ICV',
                'uuid' => (string) $chain['icv'],
            ];
        }

        if (!$hasPIH) {
            $invoice['additionalDocuments'][] = [
                'id' => 'PIH',
                'attachment' => [
                    'content' => empty($chain['previous_hash'])
                        ? getInitialPIH()
                        : $chain['previous_hash']
                ]
            ];
        }

        $invoice['invoice_chain'] = $chain;

        return $invoice;
    }
}