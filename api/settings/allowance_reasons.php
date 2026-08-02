<?php
declare(strict_types=1);
use App\Repositories\AllowanceReasonRepository;
use App\Repositories\CompanyStorageRepository;
require_once __DIR__ . '/../../includes/api_bootstrap.php';
try {
    $companyRepository = new CompanyStorageRepository();
    $company = $companyRepository->loadCurrentCompany();
    $repository = new AllowanceReasonRepository();
    jsonResponse(
        true,
        'Success',
        [
            'allowances' => $repository->getAllowanceReasons((int)$company['id']),
            'charges' => $repository->getChargeReasons((int)$company['id']),
        ]
    );
} catch (Throwable $e) {
    jsonResponse(
        false,
        $e->getMessage(),
        [],
        500
    );
}