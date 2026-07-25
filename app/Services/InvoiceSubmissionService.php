<?php

namespace App\Services;

use App\Repositories\CertificateStorageRepository;
use App\Repositories\CompanyStorageRepository;
use Saleh7\Zatca\ZatcaAPI;

class InvoiceSubmissionService
{
    protected CompanyStorageRepository $storage;
    private CertificateStorageRepository $certificateRepository;

    public function __construct()
    {
        $this->storage = new CompanyStorageRepository();
        $this->certificateRepository = new CertificateStorageRepository($this->storage);
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
            $this->storage->saveClearedInvoice(
                $package['signed_xml_path'],
                $package['invoice_id'],
                $result['cleared_xml']
            );
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