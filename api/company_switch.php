<?php
/**
 * Company Switch API
 *
 * Sets the current active company.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

requirePostRequest();

$crn = trim($_POST['crn'] ?? '');

if (empty($crn)) {
    jsonResponse([
        'success' => false,
        'message' => 'Company CRN is required.'
    ], 400);
}

if (!(new App\Repositories\CompanyStorageRepository())->companyExists($crn)) {
    jsonResponse([
        'success' => false,
        'message' => 'Company not found.'
    ], 404);
}

(new App\Repositories\CompanyStorageRepository())->setCurrentCompany($crn);

jsonResponse([
    'success' => true,
    'message' => 'Current company updated successfully.',
    'data' => (new App\Repositories\CompanyStorageRepository())->getCurrentCompanyInfo()
]);