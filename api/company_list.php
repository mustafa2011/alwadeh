<?php
/**
 * Company List API
 *
 * Returns all registered companies.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$companies = getAllCompanies();

$currentCompany = getCurrentCompany();

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