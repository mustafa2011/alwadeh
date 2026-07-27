<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CompanyStorageRepository;
use App\Repositories\ItemRepository;
use App\Validators\ItemValidator;

try {

    requirePostRequest();

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        throw new Exception('Invalid item data.');
    }

    if (empty($data['id'])) {
        throw new Exception('Item id is required.');
    }

    $companyRepository = new CompanyStorageRepository();

    $company = $companyRepository->loadCurrentCompany();

    $data['company_id'] = (int)$company['id'];

    $validator = new ItemValidator();

    $validator->validate($data);

    $repository = new ItemRepository();

    $repository->update(
        (int)$data['id'],
        $data
    );

    jsonResponse(
        true,
        'Item updated successfully.'
    );

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        500
    );

}