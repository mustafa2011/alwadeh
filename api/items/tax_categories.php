<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\ItemTaxCategoryRepository;
use App\Repositories\CompanyStorageRepository;
try {
    $companyRepository = new CompanyStorageRepository();
    $company = $companyRepository->loadCurrentCompany();
    $repository = new ItemTaxCategoryRepository();

    jsonResponse(
        true,
        'Tax categories loaded successfully.',
        $repository->all((int)$company['id'])
    );

} catch (Throwable $e) {

    jsonResponse(false, $e->getMessage(), [], 500);

}