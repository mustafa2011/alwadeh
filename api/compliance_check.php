<?php
require_once __DIR__ . '/../includes/api_bootstrap.php';
use App\Services\CertificateService;
requirePostRequest();
try {
    $service = new CertificateService();
    $result = $service->runComplianceCheck();
    jsonResponse(
        $result['success'],
        $result['message'],
        $result['data'] ?? []
    );
} catch (\Saleh7\Zatca\Exceptions\ZatcaApiException $e) {
    jsonResponse(
        false,
        $e->getMessage(),
        method_exists($e,'getContext') ? $e->getContext() : []
    );
} catch (\Throwable $e) {
    jsonResponse(
        false,
        $e->getMessage(),
        [
            'file'=>$e->getFile(),
            'line'=>$e->getLine()
        ]
    );
}