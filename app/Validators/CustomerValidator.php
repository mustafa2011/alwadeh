<?php

namespace App\Validators;

use Exception;

class CustomerValidator
{
    public function validate(array $data): void
    {
        if (empty($data['customer_name'])) {
            throw new Exception('Customer name is required.');
        }
    
        if (empty($data['customer_type'])) {
            throw new Exception('Customer type is required.');
        }
    
        if (($data['customer_type'] ?? '') === 'company') {
    
            if (empty($data['registration_name'])) {
                throw new Exception('Registration name is required.');
            }
    
            if (empty($data['vat_number'])) {
                throw new Exception('VAT Number is required for company.');
            }
    
            if (empty($data['commercial_registration_number'])) {
                throw new Exception('Commercial Registration Number is required for company.');
            }
    
            if (empty($data['street_name'])) {
                throw new Exception('Street name is required.');
            }
    
            if (empty($data['building_number'])) {
                throw new Exception('Building number is required.');
            }
    
            if (empty($data['city_name'])) {
                throw new Exception('City name is required.');
            }
    
            if (empty($data['postal_zone'])) {
                throw new Exception('Postal code is required.');
            }
    
        }
    }
}