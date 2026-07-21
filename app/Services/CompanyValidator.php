<?php

namespace App\Services;

class CompanyValidator
{
    public function validate(array $data): array
    {
        $errors = [];
    
        try {
    
            (new \Saleh7\Zatca\CertificateBuilder())
                ->setOrganizationIdentifier($data['organization_identifier'] ?? '')
                ->setSerialNumber(
                    $data['solution_name'] ?? 'ALWADEH',
                    $data['model'] ?? 'ERP',
                    $data['serial_number'] ?? ''
                )
                ->setCommonName($data['common_name'] ?? '')
                ->setCountryName($data['country'] ?? 'SA')
                ->setOrganizationName($data['organization_name'] ?? '')
                ->setOrganizationalUnitName($data['organizational_unit_name'] ?? '')
                ->setAddress($data['address'] ?? '')
                ->setBusinessCategory($data['business_category'] ?? '');
    
        } catch (\Throwable $e) {
    
            $errors['general'] = $e->getMessage();
    
        }
    
        return $errors;
    }

    public function passes(array $data): bool
    {
        return empty($this->validate($data));
    }
}