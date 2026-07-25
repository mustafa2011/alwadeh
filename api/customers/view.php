<?php

require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Repositories\CustomerRepository;

try {

    $id = (int)($_GET['id'] ?? 0);

    if(!$id){
        throw new Exception('Customer id required.');
    }

    $repository = new CustomerRepository();

    jsonResponse(
        true,
        'Customer loaded successfully.',
        $repository->findForInvoice($id)
    );

} catch(Throwable $e){

    jsonResponse(
        false,
        $e->getMessage()
    );

}