<?php

/**
 * ============================================================================
 * ALWADEH ZATCA
 * Dashboard API
 * ----------------------------------------------------------------------------
 * Returns summary information required by the dashboard.
 *
 * Current Version:
 *     Version 1
 *
 * Future:
 *     - CSR Status
 *     - Compliance Status
 *     - Production Certificate Status
 *     - Invoice Statistics
 *     - Last Invoice
 * ============================================================================
 */

require_once __DIR__ . '/../includes/api_bootstrap.php';

try
{

    /*
    |--------------------------------------------------------------------------
    | Load Companies and Current Company
    |--------------------------------------------------------------------------
    */

    $companies = getAllCompanies();
    $currentCompanyCrn = getCurrentCompany();

    $currentCompany = null;

    foreach ($companies as $company)
    {
        if (($company['crn'] ?? '') === $currentCompanyCrn)
        {
            $currentCompany = $company;
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Response
    |--------------------------------------------------------------------------
    */

    jsonResponse(
        true,
        '',
        [
    
            'currentVersion' => '3.0',
    
            'companiesCount' => count($companies),
    
            'companyName' => $currentCompany['company_name'] ?? null,
    
            'environment' => $currentCompany['environment'] ?? null,
    
            /*
             |--------------------------------------------------------------
             | Certificate Status
             |--------------------------------------------------------------
             */

            'status' => $currentCompany['status'] ?? [],
        
            /*
             |--------------------------------------------------------------
             | Invoice
             |--------------------------------------------------------------
             */
    
            'invoiceCount' => 0,
    
            'lastInvoice' => null
    
        ]
    );

}
catch (Throwable $e)
{

    jsonResponse(
        false,
        $e->getMessage(),
        [],
        500
    );

}