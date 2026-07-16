<?php

/**
 * Generate Certificate
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

use App\Services\CertificateService;

requirePostRequest();

try {

    $service = new CertificateService();

    $result = $service->generateCSR($_POST);

    jsonResponse($result);

} catch (Exception $e) {

    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);

}