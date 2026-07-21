<?php

namespace App\Services;
use App\Repositories\CompanyStorageRepository;

// require_once __DIR__ . '/../../includes/api_bootstrap.php';

class CompanyCompletionService
{
    protected array $company = [];
    public function __construct()
    {
    }

    public function getReport(int $companyId): array
    {
        $company = (new CompanyStorageRepository())->loadCurrentCompany();

        $checks = [
            'party' => [
                'company_name',
                'registration_name'
            ],
            'address' => [
                'street_name',
                'building_number',
                'city_name',
                'postal_zone',
                'country_code'
            ],
            'tax_scheme' => [
                'tax_scheme_id',
                'tax_scheme_name'
            ],
            'legal_entity' => [
                'registration_name',
                'company_id'
            ],
            'certificate' => [
                'private_key',
                'csr',
                'certificate'
            ],
            'zatca' => [
                'compliance_request_id',
                'binary_security_token',
                'secret',
                'production_csid'
            ]
        ];

        $total = 0;
        $completed = 0;
        $sections = [];

        foreach ($checks as $section => $fields) {
            $sections[$section] = [];

            foreach ($fields as $field) {
                $total++;

                $value = null;

                switch ($section) {
                    case 'party':
                        $value = $company['party'][$field] ?? ($company[$field] ?? null);
                        break;
                
                    case 'address':
                        $value = $company['address'][$field] ?? null;
                        break;
                
                    case 'tax_scheme':
                        $value = $company['tax_scheme'][$field] ?? null;
                        break;
                
                    case 'legal_entity':
                        $value = $company['legal_entity'][$field] ?? null;
                        break;
                
                    case 'certificate':
                        $value = $company['certificate'][$field] ?? null;
                        break;
                
                    case 'zatca':
                        $value = $company['zatca'][$field] ?? null;
                        break;
                }
                
                $status = !empty($value);
                if ($status) {
                    $completed++;
                }

                $sections[$section][$field] = $status;
            }
        }

        $percentage = $total ? round(($completed / $total) * 100) : 0;

        return [
            'sections' => $sections,
            'percentage' => $percentage,
            'ready_for_compliance' => $percentage >= 70,
            'ready_for_production' => $percentage === 100
        ];
    }

}