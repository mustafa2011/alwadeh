<?php
/**
 * Company Details API
 *
 * Returns company information.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

$crn = trim($_GET['crn'] ?? '');

if (empty($crn)) {
    $crn = getCurrentCompany();
}

if (!$crn) {
    jsonResponse([
        'success' => false,
        'message' => 'No company selected.'
    ], 400);
}

$company = getCompany($crn);

if (!$company) {
    jsonResponse([
        'success' => false,
        'message' => 'Company not found.'
    ], 404);
}

jsonResponse([
    'success' => true,
    'message' => '',
    'data' => $company
]);