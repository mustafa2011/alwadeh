<?php
/**
 * Company Save API.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

requirePostRequest();

$data = [
    'crn'               => trim($_POST['crn'] ?? ''),
    'vat'               => trim($_POST['vat'] ?? ''),
    'company_name'      => trim($_POST['company_name'] ?? ''),
    'branch_name'       => trim($_POST['branch_name'] ?? ''),
    'environment'       => trim($_POST['environment'] ?? 'nonprod'),
    'street'            => trim($_POST['street'] ?? ''),
    'building_number'   => trim($_POST['building_number'] ?? ''),
    'subdivision'       => trim($_POST['subdivision'] ?? ''),
    'city'              => trim($_POST['city'] ?? ''),
    'postal_zone'       => trim($_POST['postal_zone'] ?? ''),
    'business_category' => trim($_POST['business_category'] ?? ''),
];



try {

    if ((new App\Repositories\CompanyStorageRepository())->companyExists($data['crn'])) {

        (new App\Repositories\CompanyStorageRepository())->updateCompany(
            $data['crn'],
            $data
        );

        $message = 'Company updated successfully.';

    } else {

        (new App\Repositories\CompanyStorageRepository())->createCompany($data);

        $message = 'Company created successfully.';
    }

    (new App\Repositories\CompanyStorageRepository())->setCurrentCompany($data['crn']);

    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => (new App\Repositories\CompanyStorageRepository())->getCompany($data['crn'])
    ]);

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        400
    );
}