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

    validateCompanyData($data);

    if (companyExists($data['crn'])) {

        updateCompany(
            $data['crn'],
            $data
        );

        $message = 'Company updated successfully.';

    } else {

        createCompany($data);

        $message = 'Company created successfully.';
    }

    setCurrentCompany($data['crn']);

    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => getCompany($data['crn'])
    ]);

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        400
    );
}