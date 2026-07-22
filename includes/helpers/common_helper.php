<?php
/**
 * Common Helper Functions
 *
 * Contains generic helper functions shared across the project.
 *
 * Responsibilities:
 * - JSON responses
 * - UUID generation
 * - Environment handling
 * - HTTP request validation
 * - Common utility functions
 */

 
 use Saleh7\Zatca\CertificateBuilder;
 use App\Core\Database;


if (!function_exists('jsonResponse')) {

    /**
     * Send JSON response and terminate execution.
     *
     * @param bool   $success
     * @param string $message
     * @param array  $data
     * @return void
     */
    function jsonResponse($success, $message = null, $data = [], $statusCode = 200)
    {
        if (is_array($success) && $message === null) {
    
            http_response_code($statusCode);
    
            header('Content-Type: application/json; charset=UTF-8');
    
            echo json_encode(
                $success,
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
    
            exit;
        }
    
        http_response_code($statusCode);
    
        header('Content-Type: application/json; charset=UTF-8');
    
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
        exit;
    }
}

if (!function_exists('generateUUID')) {

    /**
     * Generate a Version 4 UUID.
     *
     * @return string
     * @throws Exception
     */
    function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}

if (!function_exists('requirePostRequest')) {

    /**
     * Ensure the request method is POST.
     *
     * @return void
     */
    function requirePostRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.', [], 405);
        }
    }
}

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
        $environment = match ($settings['environment'] ?? 'simulation') {
            'nonprod'    => 'sandbox',
            'simulation' => 'simulation',
            'production' => 'production',
            default      => 'sandbox'
        };
        if (empty($settings['environment'])) {
            throw new Exception(
                'Environment not found in certificate settings.'
            );
        }

        return $environment;
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

if(!function_exists('getDatabaseEnvironment')) {
    /**
     * Get environment value compatible with Database.
     *
     * @return string
     * @throws Exception
     */
    function getDatabaseEnvironment()
    {
        switch (getEnvironment()) {
    
            case CertificateBuilder::ENV_NONPROD:
                return 'nonprod';
    
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

function loadCertificateSettings($settingsFile = null): array
{
    $pdo = Database::getConnection();
    $company = (new App\Repositories\CompanyStorageRepository())->loadCurrentCompany();

    if (empty($company['id'])) {
        return [];
    }


    $stmt = $pdo->prepare("
        SELECT *
        FROM company_zatca_settings
        WHERE company_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $company['id']
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

