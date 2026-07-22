<?php

require_once __DIR__ . '/../includes/api_bootstrap.php';

use App\Services\CertificateService;

requirePostRequest();


try {

    $service = new CertificateService();

    $result = $service->requestComplianceCertificate(
        $_POST['otp'] ?? ''
    );

    jsonResponse(
        $result['success'],
        $result['message'],
        $result['data'] ?? []
    );

} catch (\Saleh7\Zatca\Exceptions\ZatcaApiException $e) {

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