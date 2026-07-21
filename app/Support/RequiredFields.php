<?php

namespace App\Support;

class RequiredFields
{
    public const COMPANY = [
        'organization_name'       => 'Company name is required.',
        'organization_identifier' => 'VAT number is required.',
        'crn'                     => 'Commercial Registration is required.',
        'street'                  => 'Street is required.',
        'building_number'         => 'Building number is required.',
        'city'                    => 'City is required.',
        'postal_zone'             => 'Postal code is required.',
        'business_category'       => 'Business category is required.',
    ];

    public const CUSTOMER = [
        'registration_name' => 'Customer name is required.',
    ];

    public const INVOICE = [
        'invoice_number' => 'Invoice number is required.',
        'issue_date'     => 'Invoice date is required.',
    ];

    public const ITEM = [
        'name'  => 'Item name is required.',
        'price' => 'Item price is required.',
    ];
}