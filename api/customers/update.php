<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CustomerRepository;
use App\Validators\CustomerValidator;

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($data)) {
        throw new Exception('Invalid customer data.');
    }

    if (empty($data['id'])) {
        throw new Exception('Customer ID is required.');
    }

    $validator = new CustomerValidator();
    $validator->validate($data);

    $repository = new CustomerRepository();

    $repository->update(
        (int)$data['id'],
        $data
    );

    jsonResponse(
        true,
        'Customer updated successfully.'
    );

} catch (Throwable $e) {

    jsonResponse(
        false,
        $e->getMessage()
    );

}