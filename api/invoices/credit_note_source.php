<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';

use App\Core\Database;
use App\Repositories\InvoiceRepository;

try {
    $id=(int)($_GET['id'] ?? 0);

    if(!$id){
        throw new Exception('Invalid invoice id.');
    }

    $repo=new InvoiceRepository(Database::getConnection());

    $invoice=$repo->findById($id);

    if(!$invoice){
        throw new Exception('Invoice not found.');
    }

    $invoice['items']=$repo->findRemainingLines($id);
    $invoice['remaining']=$repo->findRemainingData($id);
    $invoice['allowance_charges']=$repo->findAllowanceCharges($id);

    jsonResponse(
        true,
        'Invoice loaded successfully.',
        $invoice
    );
}
catch(Throwable $e){
    jsonResponse(
        false,
        $e->getMessage()
    );
}