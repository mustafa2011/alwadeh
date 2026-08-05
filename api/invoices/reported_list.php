<?php
require_once __DIR__ . '/../../includes/api_bootstrap.php';
use App\Repositories\InvoiceRepository;
use App\Repositories\CompanyStorageRepository;
use App\Core\Database;
try {
    $storageRepository = new CompanyStorageRepository();
    $company = $storageRepository->loadCurrentCompany();
    if(!$company){
        throw new Exception('Company not found.');
    }
    $repository=new InvoiceRepository(
        Database::getConnection()
    );
    echo json_encode([
        'success'=>true,
        'data'=>$repository->findReportedInvoices(
            (int)$company['id']
        )
    ],
    JSON_UNESCAPED_UNICODE);
}
catch(Throwable $e){
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ],
    JSON_UNESCAPED_UNICODE);
}