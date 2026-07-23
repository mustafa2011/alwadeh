<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\InvoiceRepository;
use PDO;
use Saleh7\Zatca\ZatcaAPI;

class InvoiceChainService
{
    private InvoiceRepository $invoiceRepository;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->invoiceRepository = new InvoiceRepository($this->db);
    }

    public function next(int $companyId): array
    {
        $lastInvoice = $this->invoiceRepository->findLastIssuedInvoice($companyId);

        return [
            'icv' => $lastInvoice
                ? ((int) $lastInvoice['icv']) + 1
                : 1,

            'previous_hash' => $lastInvoice['invoice_hash']
                ?? 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ=='
        ];
    }

    public function api(): ZatcaAPI
    {
        return new ZatcaAPI(
            getApiEnvironment()
        );
    }
}