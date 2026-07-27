<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Repositories\ItemRepository;
use App\Validators\ItemValidator;
use App\Repositories\CompanyStorageRepository;
try {
    requirePostRequest();
    $data = json_decode(
        file_get_contents('php://input'),
        true
    );
    if (!is_array($data)) {
        throw new Exception('Invalid item data.');
    }
    $companyRepository = new CompanyStorageRepository();
    $company = $companyRepository->loadCurrentCompany();
    $data['company_id'] = (int)$company['id'];
    $validator = new ItemValidator();
    $validator->validate($data);
    $repository = new ItemRepository();
    $id = $repository->create($data);
    jsonResponse(
        true,
        'Item created successfully.',
        [
            'id' => $id
        ]
    );
} catch (Throwable $e) {
    jsonResponse(false, $e->getMessage(), [], 500);
}