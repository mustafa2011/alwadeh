<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
requirePostRequest();
$crn = trim($_POST['crn'] ?? '');
if (empty($crn)) {
    jsonResponse(false, 'CRN is required.', [], 400);
}
if (!(new App\Repositories\CompanyStorageRepository)->companyExists($crn)) {
    jsonResponse(false, 'Company not found.', [], 404);
}
(new App\Repositories\CompanyStorageRepository)->deleteCompany($crn);
jsonResponse(true, 'Company deleted successfully.');