<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CustomerRepository;

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if(empty($data['id'])){
        throw new Exception('Customer id is required.');
    }

    $repository = new CustomerRepository();

    $repository->delete((int)$data['id']);

    jsonResponse(
        true,
        'Customer deleted successfully.'
    );

} catch(Throwable $e){

    jsonResponse(
        false,
        $e->getMessage()
    );

}