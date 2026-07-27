<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CompanyStorageRepository;
use App\Repositories\ItemRepository;

try {

    requirePostRequest();

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        throw new Exception('Invalid request.');
    }

    if (empty($data['id'])) {
        throw new Exception('Item id is required.');
    }

    $companyRepository = new CompanyStorageRepository();

    $company = $companyRepository->loadCurrentCompany();

    $repository = new ItemRepository();

    $repository->delete(
        (int)$company['id'],
        (int)$data['id']
    );

    jsonResponse(
        true,
        'Item deleted successfully.'
    );

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        500
    );

}