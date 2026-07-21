<?php
/**
 * File Helper Functions
 *
 */

if (!function_exists('getCSRFile')) {

    /**
     * Get CSR file path.
     *
     * @return string
     */
    function getCSRFile(): string
    {
        return (new App\Repositories\CompanyStorageRepository())->companyFile('certificate.csr');
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
        return (new App\Repositories\CompanyStorageRepository())->companyFile('private.pem');
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
        return (new App\Repositories\CompanyStorageRepository())->compliancePath();
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
        return (new App\Repositories\CompanyStorageRepository())->invoicesPath();
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
        return (new App\Repositories\CompanyStorageRepository())->logsPath();
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
        return (new App\Repositories\CompanyStorageRepository())->backupPath();
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
        $crn = (new App\Repositories\CompanyStorageRepository())->getCurrentCompany();
    
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