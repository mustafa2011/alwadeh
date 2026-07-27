<?php
require_once __DIR__.'/../../includes/api_bootstrap.php';
use App\Repositories\ItemRepository;
use App\Repositories\CompanyStorageRepository;
try {
    $companyRepository = new CompanyStorageRepository();
    $company = $companyRepository->loadCurrentCompany();
    $repository = new ItemRepository();
    $items = $repository->all((int)$company['id']);
    jsonResponse(
        true,
        'Items loaded successfully.',
        $items
    );
} catch (Throwable $e){
    jsonResponse(
        false,
        $e->getMessage(),
        [],
        500
    );
}