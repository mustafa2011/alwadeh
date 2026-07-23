<?php

namespace App\Services;
use App\Repositories\CertificateStorageRepository;

class InvoiceSubmissionService
{
    private CertificateStorageRepository $certificateRepository;

    public function __construct()
    {
        $this->certificateRepository = new CertificateStorageRepository();
    }    
    public function submit(
        \Saleh7\Zatca\ZatcaAPI $api,
        array $package,
        bool $isSimplified
    ): array {
    
        $credentials = $this->certificateRepository->loadProductionCredentials();
    
        return submitInvoice(
            $api,
            $credentials,
            $package['signed_xml'],
            $package['hash'],
            $package['uuid'],
            $isSimplified
        );
    }
}