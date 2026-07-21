<?php

namespace App\Validators;
use Exception;

class CertificateValidator
{
    public function __construct(){}

    public function validateCSR(array $data): void
    {
        /**
         * the following commented to support Unified number start with 7 later
         * if (!preg_match('/^[17]\d{9}$/', $crn)) {
         *
         *     throw new Exception(
         *         'Commercial Registration/Unified Number must be 10 digits and start with 1 or 7.'
         *     );
         * }
         *
        */

        if (!preg_match('/^1\d{9}$/', $data['crn'])) {
            throw new Exception('CR number is 10 digits start with 1.');
        } 

        if (!preg_match('/^\d{5}$/', $data['postal_zone'])) {
            throw new Exception('Postal code must be exactly 5 digits.');
        }

        $shortAddress = strtoupper(trim($data['address']));

        if (!preg_match('/^[A-Z]{4}\d{4}$/', $shortAddress)) {
            throw new Exception(
                'National Short Address must contain 4 letters followed by 4 digits.'
            );
        }

        $buildingNumber = trim($data['building_number']);

        if (substr($shortAddress, -4) !== $buildingNumber) {
            throw new Exception(
                'Building number must match last 4 digint in National Short Address.'
            );
        }

        if (empty($data['subdivision'])) {
            throw new Exception('Bransh name|Subdivision is required.');
        } 

        if (!preg_match('/^3\d{13}3$/', $data['organization_identifier'])) {
            throw new Exception(
                'VAT must be exactly 15 digits and start/end with 3.'
            );
        }

    }    
}