<?php
/**
 * Company Save API
 *
 * Creates or updates a company.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

requirePostRequest();

$data = [

    'crn'          => trim($_POST['crn'] ?? ''),
    'vat'          => trim($_POST['vat'] ?? ''),
    'company_name' => trim($_POST['company_name'] ?? ''),
    'branch_name'  => trim($_POST['branch_name'] ?? ''),
    'environment'  => trim($_POST['environment'] ?? 'sandbox'),

];

// Convert UI environment to internal environment.
// $data['environment'] = mapEnvironmentFromUi($data['environment']);

try {

    validateCompanyData($data);

} catch (Exception $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        400
    );

}

$isNew = !companyExists($data['crn']);

if ($isNew) {

    createCompany($data);

} else {

    updateCompany(
        $data['crn'],
        $data
    );

}

jsonResponse([
    'success' => true,
    'message' => $isNew
        ? 'Company created successfully.'
        : 'Company updated successfully.',
    'data' => getCompany($data['crn'])
]);