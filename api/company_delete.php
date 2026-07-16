<?php
/**
 * Company Delete API
 *
 * Deletes a company.
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

requirePostRequest();

$crn = trim($_POST['crn'] ?? '');

if (empty($crn)) {
    jsonResponse([
        'success' => false,
        'message' => 'CRN is required.'
    ], 400);
}

if (!companyExists($crn)) {
    jsonResponse([
        'success' => false,
        'message' => 'Company not found.'
    ], 404);
}

deleteCompany($crn);

jsonResponse([
    'success' => true,
    'message' => 'Company deleted successfully.'
]);