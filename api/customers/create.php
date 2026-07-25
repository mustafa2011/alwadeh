<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CustomerRepository;

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if(!is_array($data)){
        throw new Exception('Invalid customer data.');
    }

    $repository = new CustomerRepository();

    $id = $repository->create($data);

    jsonResponse(
        true,
        'Customer created successfully.',
        [
            'id'=>$id
        ]
    );

} catch(Throwable $e){

    jsonResponse(
        false,
        $e->getMessage()
    );

}