<?php

namespace App\Builders;

use Saleh7\Zatca\CertificateBuilder;

class CertificateBuilderFactory
{
    public function create(
        array $company,
        array $data,
        string $uuid,
        string $commonName
    ): CertificateBuilder {

        return (new CertificateBuilder())
            ->setOrganizationIdentifier(
                $company['tax_scheme']['company_id_value']
            )
            ->setSerialNumber(
                $data['serial_1'],
                $data['serial_2'],
                $uuid
            )
            ->setCommonName($commonName)
            ->setCountryName(
                $company['address']['country_identification_code'] ?? 'SA'
            )
            ->setOrganizationName(
                $company['legal_entity']['registration_name']
            )
            ->setOrganizationalUnitName(
                $data['organizational_unit_name']
            )
            ->setAddress($data['address'])
            ->setInvoiceType($data['invoice_type'])
            ->setEnvironment($data['environment'])
            ->setBusinessCategory($data['business_category']);
    }
}