<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CustomerRepository;

try {

    if (empty($_GET['id'])) {
        throw new Exception('Customer ID is required.');
    }

    $repository = new CustomerRepository();

    jsonResponse(
        true,
        'Customer loaded successfully.',
        $repository->find((int)$_GET['id'])
    );

} catch (Throwable $e) {

    jsonResponse(false, $e->getMessage());

}