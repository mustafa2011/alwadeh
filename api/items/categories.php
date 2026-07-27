<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Repositories\ItemCategoryRepository;
use App\Repositories\CompanyStorageRepository;
try {
    $companyRepository = new CompanyStorageRepository();
    $company = $companyRepository->loadCurrentCompany();
    $repository = new ItemCategoryRepository();

    jsonResponse(
        true,
        'Categories loaded successfully.',
        $repository->all((int)$company['id'])
    );

} catch (Throwable $e) {

    jsonResponse(false, $e->getMessage(), [], 500);

}
