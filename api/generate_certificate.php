<?php

require_once __DIR__ . '/../includes/api_bootstrap.php';

use App\Services\CertificateService;
use Saleh7\Zatca\Exceptions\ZatcaApiException;

requirePostRequest();

try {

    $service = new CertificateService();

    $result = $service->generateCSR($_POST);

    jsonResponse(
        $result['success'],
        $result['message'],
        $result['data'] ?? []
    );

} catch (ZatcaApiException $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        $e->getContext()
    );

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage()
    );
}