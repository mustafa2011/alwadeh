<?php

require_once __DIR__ . '/../includes/api_bootstrap.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$companies = (new App\Repositories\CompanyStorageRepository)->getAllCompanies();

$currentCompany = (new App\Repositories\CompanyStorageRepository)->getCurrentCompany();

foreach ($companies as &$company) {
    
    $company['is_current'] =
        isset($company['crn']) &&
        $company['crn'] === $currentCompany;
}

unset($company);


jsonResponse([
    'success' => true,
    'count'   => count($companies),
    'data'    => $companies
]);