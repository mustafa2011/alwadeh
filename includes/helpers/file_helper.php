<?php
/**
 * File Helper Functions
 *
 * Handles all filesystem operations used by the project.
 *
 * Responsibilities:
 * - Reading JSON files
 * - Saving JSON files
 * - Output directories
 * - Certificate file locations
 * - Storage paths
 */

if (!function_exists('getCertificateSettingsFile')) {

    /**
     * Get certificate settings file path.
     *
     * @return string
     */
    function getCertificateSettingsFile(): string
    {
        return companyFile('certificate_settings.json');
    }
}

if (!function_exists('getCSRFile')) {

    /**
     * Get CSR file path.
     *
     * @return string
     */
    function getCSRFile(): string
    {
        return companyFile('certificate.csr');
    }
}

if (!function_exists('getPrivateKeyFile')) {

    /**
     * Get private key file path.
     *
     * @return string
     */
    function getPrivateKeyFile(): string
    {
        return companyFile('private.pem');
    }
}

if (!function_exists('getComplianceCertificateFile')) {

    /**
     * Get compliance certificate JSON file path.
     *
     * @return string
     */
    function getComplianceCertificateFile(): string
    {
        return companyFile('ZATCA_certificate_data.json');
    }
}

if (!function_exists('getComplianceDirectory')) {

    /**
     * Get compliance directory.
     *
     * @return string
     */
    function getComplianceDirectory(): string
    {
        return compliancePath();
    }
}

if (!function_exists('getInvoicesDirectory')) {

    /**
     * Get invoices directory.
     *
     * @return string
     */
    function getInvoicesDirectory(): string
    {
        return invoicesPath();
    }
}

if (!function_exists('getLogsDirectory')) {

    /**
     * Get logs directory.
     *
     * @return string
     */
    function getLogsDirectory(): string
    {
        return logsPath();
    }
}

if (!function_exists('getBackupDirectory')) {

    /**
     * Get backup directory.
     *
     * @return string
     */
    function getBackupDirectory(): string
    {
        return backupPath();
    }
}
 
if (!function_exists('loadJsonFile')) {

    /**
     * Load JSON file.
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    function loadJsonFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("JSON file not found: {$filePath}");
        }

        $data = json_decode(file_get_contents($filePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON file: {$filePath}");
        }

        return $data;
    }
}

if (!function_exists('saveJsonFile')) {

    /**
     * Save data as JSON file.
     *
     * @param string $filePath
     * @param array  $data
     * @return void
     * @throws Exception
     */
    function saveJsonFile($filePath, array $data)
    {
        $result = file_put_contents(
            $filePath,
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );

        if ($result === false) {
            throw new Exception(
                "Unable to write JSON file: {$filePath}"
            );
        }
    }
}

if (!function_exists('getOutputDirectory')) {

    /**
     * Get current output directory.
     *
     * @return string
     */
    function getOutputDirectory(): string
    {
        $crn = getCurrentCompany();
    
        if ($crn) {
            return getCompanyPath($crn);
        }
    
        // Legacy mode
        $path = dirname(__DIR__, 2) . '/Output';
    
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    
        return $path;
    }
}

if (!function_exists('getOutputFile')) {

    /**
     * Get full path for an output file.
     *
     * @param string $file
     * @return string
     */

    function getOutputFile(string $file): string
    {
        return getOutputDirectory() . '/' . ltrim($file, '/');
    }
}

if (!function_exists('getComplianceOutputDirectory')) {

    /**
     * Get compliance output directory.
     *
     * @return string
     */
    function getComplianceOutputDirectory(): string
    {
        $directory = getOutputDirectory() . '/compliance';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory;
    }
}

if (!function_exists('getProductionCredentialsFile')) {

    /**
     * Get production credentials output file.
     *
     * @return string
     */
    function getProductionCredentialsFile(): string
    {
        return getOutputFile('production_credentials.json');
    }
}

if (!function_exists('getCompliancePrivateKeyPath')) {

    /**
     * Get private key file path.
     *
     * @return string
     */
    function getCompliancePrivateKeyPath(): string
    {
        return getOutputFile('private.pem');
    }
}

if (!function_exists('getComplianceCertificateFile')) {

    /**
     * Get compliance credentials file.
     *
     * @return string
     */
    function getComplianceCertificateFile(): string
    {
        return getOutputFile('ZATCA_certificate_data.json');
    }
}


