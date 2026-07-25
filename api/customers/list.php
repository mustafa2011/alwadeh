<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CustomerRepository;

try {

    $repository = new CustomerRepository();

    jsonResponse(
        true,
        'Customers loaded successfully.',
        $repository->all()
    );

} catch(Throwable $e){

    jsonResponse(
        false,
        $e->getMessage()
    );

}