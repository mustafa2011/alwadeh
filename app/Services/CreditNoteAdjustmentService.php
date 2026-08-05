<?php
namespace App\Services;

class CreditNoteAdjustmentService
{
    public function adjustAllowances(
        array $originalAllowances,
        float $ratio
    ): array {
        foreach ($originalAllowances as &$allowance) {

            if (($allowance['mode'] ?? 'amount') === 'amount') {
                $allowance['value'] =
                    round(
                        ((float)$allowance['value']) * $ratio,
                        2
                    );
            }

        }

        return $originalAllowances;
    }
}