<?php
/**
 * Compliance Helper Functions
 *
 */

use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;
use Saleh7\Zatca\ZatcaAPI;

function requestProductionCertificate(
    $api,
    $credentials
) {

    $result = $api->requestProductionCertificate(

        $credentials['binary_security_token'],
        $credentials['secret'],
        $credentials['request_id']

    );

    saveProductionCredentials(
        $result->getCertificate(),
        $result->getSecret(),
        $result->getRequestId()
    );

    updateCompanyStatus(COMPANY_STATUS_PRODUCTION);

    return [

        'certificate' => $result->getCertificate(),

        'secret' => $result->getSecret(),

        'requestId' => $result->getRequestId(),

    ];

}

function createComplianceApi($environment)
{
    return new ZatcaAPI($environment);
}

function signInvoice(
    $xmlFile,
    $credentials,
    $privateKey,
    $signedFileName,
    $outputDirectory
) {

    $xmlContent = file_get_contents($xmlFile);

    if ($xmlContent === false) {
        throw new Exception(
            "Unable to read invoice XML: {$xmlFile}"
        );
    }

    $certificate = new Certificate(
        $credentials['binary_security_token'],
        $privateKey,
        $credentials['secret']
    );

    $signer = InvoiceSigner::signInvoice(
        $xmlContent,
        $certificate
    );

    $signer->saveXMLFile(
        $signedFileName,
        $outputDirectory
    );

    return [

        // Signed invoice XML content
        'signedXml' => $signer->getInvoice(),

        // Invoice hash
        'hash' => $signer->getHash(),

        // Original XML file
        'xmlFile' => $xmlFile,

        // Signed XML file path
        'signedXmlFile' =>
            $outputDirectory
            . DIRECTORY_SEPARATOR
            . $signedFileName,

    ];
}

function validateComplianceInvoice(
    $api,
    $credentials,
    $signedXml,
    $invoiceHash,
    $uuid
) {

    $result = $api->validateInvoiceCompliance(

        $credentials['binary_security_token'],

        $credentials['secret'],

        $signedXml,

        $invoiceHash,

        $uuid

    );

    return [

        'success' =>

            $result->isSuccess()
            || $result->getStatusCode() == 202,

        'statusCode' => $result->getStatusCode(),

        'status' =>

            $result->getValidationStatus()
            ?? 'UNKNOWN',

        'warnings' =>

            $result->getWarningMessages(),

        'errors' =>

            $result->getErrorMessages(),

        'response' => $result,

    ];

}

function processComplianceInvoice(
    $api,
    $invoiceData,
    $credentials,
    $privateKey,
    $outputDirectory,
    $icv
) {

    // Update ICV
    $invoiceData['additionalDocuments'][0]['uuid'] = (string) $icv;

    // Generate XML
    $xmlFile = generateInvoiceXml(
        $invoiceData,
        $outputDirectory
    );

    // Sign XML
    $signed = signInvoice(
        $xmlFile,
        $credentials,
        $privateKey,
        $invoiceData['id'] . '_signed.xml',
        $outputDirectory
    );

    // Validate
    $validation = validateComplianceInvoice(
        $api,
        $credentials,
        $signed['signedXml'],
        $signed['hash'],
        $invoiceData['uuid']
    );

    return [

        'success' => $validation['success'],

        'status' => $validation['status'],

        'statusCode' => $validation['statusCode'],

        'warnings' => $validation['warnings'],

        'errors' => $validation['errors'],

        'uuid' => $invoiceData['uuid'],

        'invoiceId' => $invoiceData['id'],

        'hash' => $signed['hash'],

        'xmlFile' => $signed['xmlFile'],

        'signedXmlFile' => $signed['signedXmlFile'],

    ];

}

