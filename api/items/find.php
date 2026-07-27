<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CompanyStorageRepository;
use App\Repositories\ItemRepository;

try {

    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invalid item id.');
    }

    $companyRepository = new CompanyStorageRepository();

    $company = $companyRepository->loadCurrentCompany();

    $repository = new ItemRepository();

    $item = $repository->find(
        (int)$company['id'],
        $id
    );

    if (!$item) {
        throw new Exception('Item not found.');
    }

    jsonResponse(
        true,
        'Item loaded successfully.',
        $item
    );

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        500
    );

}