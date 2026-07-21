<?php

require_once __DIR__ . '/../includes/api_bootstrap.php';

requirePostRequest();

$crn = trim($_POST['crn'] ?? '');

if (empty($crn)) {
    jsonResponse([
        'success' => false,
        'message' => 'CRN is required.'
    ], 400);
}

if (!(new App\Repositories\CompanyStorageRepository())->companyExists($crn)) {
    jsonResponse([
        'success' => false,
        'message' => 'Company not found.'
    ], 404);
}

(new App\Repositories\CompanyStorageRepository())->deleteCompany($crn);

jsonResponse([
    'success' => true,
    'message' => 'Company deleted successfully.'
]);