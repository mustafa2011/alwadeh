<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
requirePostRequest();
$crn = trim($_POST['crn'] ?? '');
if (empty($crn)) {
    jsonResponse(false, 'Company CRN is required.', [], 400);
}
if (!(new App\Repositories\CompanyStorageRepository())->companyExists($crn)) {
    jsonResponse(false, 'Company not found.', [], 404);
}
(new App\Repositories\CompanyStorageRepository())->setCurrentCompany($crn);
jsonResponse(
    true, 
    'Current company updated successfully.', 
    (new App\Repositories\CompanyStorageRepository())->getCurrentCompanyInfo()
);