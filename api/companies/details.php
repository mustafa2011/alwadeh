<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
$crn = trim($_GET['crn'] ?? '');
if (empty($crn)) {
    $crn = (new App\Repositories\CompanyStorageRepository)->getCurrentCompany();
}
if (!$crn) {
    jsonResponse(false, 'No company selected.', [], 400);
}
$company = (new App\Repositories\CompanyStorageRepository)->getCompany($crn);
if (!$company) {
    jsonResponse(false, 'Company not found.', [], 404);
}
jsonResponse(true, '', $company);