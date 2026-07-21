<?php

namespace App\Builders;

use Saleh7\Zatca\Address;
use Saleh7\Zatca\LegalEntity;
use Saleh7\Zatca\Party;
use Saleh7\Zatca\PartyTaxScheme;
use Saleh7\Zatca\TaxScheme;

class PartyBuilder
{
    public function build(array $company): Party
    {
        return (new Party())
            ->setPartyIdentification(
                $company['commercial_registration_number'] ?? null
            )
            ->setPartyIdentificationId('CRN')
            ->setPostalAddress(
                $this->address($company['address'] ?? [])
            )
            ->setPartyTaxScheme(
                $this->taxScheme($company['tax_scheme'] ?? [])
            )
            ->setLegalEntity(
                $this->legalEntity($company['legal_entity'] ?? [])
            );
    }

    private function address(array $data): Address
    {
        return (new Address())
            ->setStreetName($data['street_name'] ?? null)
            ->setBuildingNumber($data['building_number'] ?? null)
            ->setPlotIdentification($data['plot_identification'] ?? null)
            ->setCitySubdivisionName($data['city_subdivision_name'] ?? null)
            ->setCityName($data['city_name'] ?? null)
            ->setPostalZone($data['postal_zone'] ?? null)
            ->setCountry($data['country_identification_code'] ?? 'SA');
    }

    private function taxScheme(array $data): PartyTaxScheme
    {
        return (new PartyTaxScheme())
            ->setCompanyId($data['company_id_value'] ?? null)
            ->setTaxScheme(
                (new TaxScheme())
                    ->setId($data['tax_scheme_id'] ?? 'VAT')
            );
    }

    private function legalEntity(array $data): LegalEntity
    {
        return (new LegalEntity())
            ->setRegistrationName(
                $data['registration_name'] ?? null
            );
    }
}