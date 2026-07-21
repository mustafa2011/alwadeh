<?php

namespace App\Services;
use App\Repositories\CompanyStorageRepository;

class CompanyService
{
    public function __construct()
    {
    }

    function buildSupplier(): array
    {
        $company = (new CompanyStorageRepository())->loadCurrentCompany();

        return [
            'registrationName'   => $company['legal_entity']['registration_name'] ?? '',
            'taxId'              => $company['tax_scheme']['company_id_value'] ?? '',
            'identificationId'   => $company['commercial_registration_number'] ?? '',
            'identificationType' => 'CRN',
    
            'address' => [
                'street'         => $company['address']['street_name'] ?? '',
                'buildingNumber' => $company['address']['building_number'] ?? '',
                'subdivision'    => $company['address']['city_subdivision_name'] ?? '',
                'city'           => $company['address']['city_name'] ?? '',
                'postalZone'     => $company['address']['postal_zone'] ?? '',
                'country'        => $company['address']['country_identification_code'] ?? 'SA',
            ],    
            'taxScheme' => [
                'id' => $company['tax_scheme']['tax_scheme_id'] ?? 'VAT',
            ],
        ];
    }    
   

}
