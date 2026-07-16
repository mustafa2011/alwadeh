<?php
/**
 * Certificate Helper Functions
 *
 * Handles certificate generation and certificate-related data.
 *
 * Responsibilities:
 * - Supplier information
 * - Certificate settings
 * - Private key loading
 * - Compliance credentials
 * - Production credentials
 * 
 */

if (!function_exists('buildSupplier')) {

    /**
     * Build supplier array from certificate settings.
     *
     * @param array $settings
     * @return array
     */
    function buildSupplier($settings)
    {
        return [
            'registrationName'   => $settings['organization_name'],
            'taxId'              => $settings['vat_number'],
            'identificationId'   => $settings['crn'],
            'identificationType' => 'CRN',

            'address' => [
                'street'         => $settings['street'],
                'buildingNumber' => $settings['building_number'],
                'subdivision'    => $settings['subdivision'],
                'city'           => $settings['city'],
                'postalZone'     => $settings['postal_zone'],
                'country'        => 'SA',
            ],
            'taxScheme' => [
                'id' => 'VAT',
            ],
        ];
    }
}

if (!function_exists('loadComplianceCredentials')) {

    /**
     * Load compliance certificate credentials.
     *
     * @return array
     * @throws Exception
     */
    function loadComplianceCredentials()
    {
        return loadJsonFile(
            getComplianceCertificateFile()
        );
    }
}

if (!function_exists('loadPrivateKey')) {

    /**
     * Load and clean private key.
     *
     * @return string
     * @throws Exception
     */
    function loadPrivateKey()
    {
        $privateKey = file_get_contents(
            getCompliancePrivateKeyPath()
        );

        if ($privateKey === false) {
            throw new Exception('Unable to read private key.');
        }

        return trim(
            preg_replace(
                '/-----(?:BEGIN|END)(?: EC)? PRIVATE KEY-----/',
                '',
                $privateKey
            )
        );
    }
}

if (!function_exists('saveProductionCredentials')) {

    /**
     * Save production certificate credentials.
     *
     * @param string $certificate
     * @param string $secret
     * @param string $requestId
     * @return void
     * @throws Exception
     */
    function saveProductionCredentials(
        $certificate,
        $secret,
        $requestId
    ) {

        saveJsonFile(

            getProductionCredentialsFile(),

            [

                'certificate' => $certificate,

                'secret' => $secret,

                'requestId' => $requestId,

                'generated_at' => date('c'),

            ]

        );

    }
}
