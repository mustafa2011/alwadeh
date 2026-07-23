<?php

namespace App\Services;

use App\Repositories\CertificateStorageRepository;
use Saleh7\Zatca\Helpers\Certificate;
use Exception;

class InvoiceCertificateService
{
    private CertificateStorageRepository $certificateRepository;

    public function __construct()
    {
        $this->certificateRepository = new CertificateStorageRepository();
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