<?php

namespace App\Services;

use App\Repositories\CertificateStorageRepository;
use App\Repositories\CompanyStorageRepository;
use Saleh7\Zatca\Helpers\Certificate;
use Exception;

class InvoiceCertificateService
{
    protected CompanyStorageRepository $companyStorageRepository;
    private CertificateStorageRepository $certificateRepository;

    public function __construct()
    {
        $this->companyStorageRepository = new CompanyStorageRepository();
        $this->certificateRepository = new CertificateStorageRepository($this->companyStorageRepository);
    }

    public function create(): Certificate
    {
        $credentials = $this->certificateRepository->loadProductionCredentials();

        if (empty($credentials['certificate'])) {
            throw new Exception('Production certificate not found.');
        }

        if (empty($credentials['secret'])) {
            throw new Exception('Production secret not found.');
        }

        $privateKey = $this->certificateRepository->loadPrivateKey();

        if (empty($privateKey)) {
            throw new Exception('Private key not found.');
        }

        return new Certificate(
            $credentials['certificate'],
            $privateKey,
            $credentials['secret']
        );
    }
}