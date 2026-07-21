<?php

namespace App\Services;

use App\Models\Company;

class CompanyProfileService
{
    private Company $company;

    public function __construct()
    {
        $this->company = new Company();
    }

    public function load(): array
    {
        return $this->company->getActive() ?? [];
    }

    public function save(array $data): bool
    {
        $company = $this->getActive();
    
        if ($company) {
            return $this->company->update((int)$company['id'], $data);
        }
    
        return $this->company->create($data) > 0;
    }
    
    private function getActive(): ?array
    {
        return $this->company->getActive();
    }

    public function isComplete(): bool
    {
        $company = $this->load();

        $required = [
            'organization_name',
            'organization_identifier',
            'crn',
            'street',
            'building_number',
            'city',
            'postal_zone',
            'business_category'
        ];

        foreach ($required as $field) {
            if (empty($company[$field])) {
                return false;
            }
        }

        return true;
    }

    public function canIssueInvoice(): bool
    {
        $company = $this->load();

        return $this->isComplete()
            && !empty($company['certificate'])
            && !empty($company['private_key'])
            && !empty($company['secret'])
            && !empty($company['production_csid']);
    }

    public function getStatus(): string
    {
        $company = $this->load();

        if (!$this->isComplete()) {
            return 'NOT_CONFIGURED';
        }

        if (empty($company['certificate'])) {
            return 'CSR_READY';
        }

        if (empty($company['production_csid'])) {
            return 'COMPLIANCE_READY';
        }

        return 'PRODUCTION_READY';
    }
}