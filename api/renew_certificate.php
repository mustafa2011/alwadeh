<?php

require_once __DIR__ . '/../includes/api_bootstrap.php';

use App\Services\CertificateService;
use Saleh7\Zatca\Exceptions\ZatcaApiException;

requirePostRequest();


try {

    $otp = trim($_POST['otp'] ?? '');

    $service = new CertificateService();

    $result = $service->renewProductionCertificate($otp);

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