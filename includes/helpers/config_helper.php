<?php
/**
 * Config Helper Functions
 *
 * Handles certificate generation and certificate-related data.
 *
 * Responsibilities:
 * - Certificate settings
 * - Environment handling
 * - Get VAT number from settings
 * - Get Organization name from settings
 * - Get Common name from settings
 * 
 */
 
use Saleh7\Zatca\CertificateBuilder;

if (!function_exists('getEnvironment')) {

    /**
     * Get ZATCA Environment.
     *
     * @return string
     * @throws Exception
     */
    function getEnvironment()
    {
        $settings = loadCertificateSettings();

        if (empty($settings['environment'])) {
            throw new Exception(
                'Environment not found in certificate settings.'
            );
        }

        return $settings['environment'];
    }
}

if(!function_exists('getApiEnvironment')) {
    /**
     * Get environment value compatible with ZatcaAPI.
     *
     * @return string
     * @throws Exception
     */
    function getApiEnvironment()
    {
        switch (getEnvironment()) {
    
            case CertificateBuilder::ENV_NONPROD:
                return 'sandbox';
    
            case CertificateBuilder::ENV_SIMULATION:
                return 'simulation';
    
            case CertificateBuilder::ENV_PRODUCTION:
                return 'production';
    
            default:
                throw new Exception('Invalid environment.');
        }
    }
}

if (!function_exists('getCommonNameByEnvironment')) {

    /**
     * Get Common Name based on ZATCA environment.
     *
     * @param string $environment
     * @return string
     * @throws Exception
     */
    function getCommonNameByEnvironment($environment)
    {
        switch ($environment) {

            case CertificateBuilder::ENV_SIMULATION:
                return 'PREZATCA-Code-Signing';

            case CertificateBuilder::ENV_NONPROD:
                return 'TSTZATCA-Code-Signing';

            case CertificateBuilder::ENV_PRODUCTION:
                return 'ZATCA-Code-Signing';

            default:
                throw new Exception('Invalid environment.');
        }
    }
}

if (!function_exists('getVatNumber')) {

    /**
     * Get VAT Number.
     *
     * @return string
     * @throws Exception
     */
    function getVatNumber()
    {
        $settings = loadCertificateSettings();

        return $settings['vat_number'] ?? '';
    }
}

if (!function_exists('getOrganizationName')) {

    /**
     * Get Organization Name.
     *
     * @return string
     * @throws Exception
     */
    function getOrganizationName()
    {
        $settings = loadCertificateSettings();

        return $settings['organization_name'] ?? '';
    }
}

if (!function_exists('getCommonName')) {

    /**
     * Get Common Name.
     *
     * @return string
     * @throws Exception
     */
    function getCommonName()
    {
        $settings = loadCertificateSettings();

        return $settings['common_name'] ?? '';
    }
}
