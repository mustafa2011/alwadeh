<?php

namespace App\Services;

use App\Repositories\CertificateStorageRepository;
use App\Repositories\CompanyStorageRepository;
use Saleh7\Zatca\ZatcaAPI;
use App\Repositories\InvoiceRepository;
use App\Core\Database;

class InvoiceSubmissionService
{
    protected CompanyStorageRepository $storage;
    private CertificateStorageRepository $certificateRepository;
    private InvoiceRepository $invoiceRepository;

    public function __construct()
    {
        $this->storage = new CompanyStorageRepository();
        $this->certificateRepository = new CertificateStorageRepository($this->storage);
        $this->invoiceRepository = new InvoiceRepository(
            Database::getConnection()
        );
    }

    public function submit(
        ZatcaAPI $api,
        array $package
    ): array {

        $credentials = $this->certificateRepository->loadProductionCredentials();

        $isSimplified =
            ($package['invoice']['invoiceType']['invoice'] ?? null) === 'simplified';
        $result = $this->submitInvoice(
            $api,
            $credentials,
            $package['signed_xml'],
            $package['hash'],
            $package['uuid'],
            $isSimplified
        );

        if (!$isSimplified && !empty($result['cleared_xml'])) {

            $path = $this->storage->saveClearedInvoice(
                $package['signed_xml_path'],
                $package['invoice_id'],
                $result['cleared_xml']
            );
        
            $this->invoiceRepository->updateZatcaXmlPath(
                (int)$package['db_invoice_id'],
                $path
            );
        
            $result['zatca_xml_path'] = $path;
        }

        return $result;
    }

    private function submitInvoice(
        ZatcaAPI $api,
        array $credentials,
        string $signedXml,
        string $invoiceHash,
        string $uuid,
        bool $isSimplified
    ): array {

        if ($isSimplified) {
            $result = $api->submitReportingInvoice(
                $credentials['certificate'],
                $credentials['secret'],
                $signedXml,
                $invoiceHash,
                $uuid
            );

            $submissionStatus = $result->getReportingStatus();

            $success =
                $result->isReported()
                || in_array($result->getStatusCode(), [200, 202], true);
        } else {
            $result = $api->submitClearanceInvoice(
                $credentials['certificate'],
                $credentials['secret'],
                $signedXml,
                $invoiceHash,
                $uuid
            );

            $submissionStatus = $result->getClearanceStatus();

            $success =
                $result->isCleared()
                || in_array($result->getStatusCode(), [200, 202], true);
        }

        return [
            'success' => $success,
            'statusCode' => $result->getStatusCode(),
            'status' => $submissionStatus,
            'submission_type' => $isSimplified ? 'reporting' : 'clearance',
            'warnings' => $result->getWarningMessages(),
            'errors' => $result->getErrorMessages(),
            'cleared_xml' => !$isSimplified && $result->isCleared()
                ? $result->getDecodedClearedInvoice()
                : null,
            'response' => $result->toArray()
        ];
    }
}