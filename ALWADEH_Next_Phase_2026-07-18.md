# ALWADEH -- Progress Summary (2026-07-18)

## Completed

-   Migrated company loading to MySQL.
-   `loadCurrentCompany()` now loads:
    -   companies
    -   company_party
    -   company_address
    -   company_tax_scheme
    -   company_legal_entity
-   Fixed `buildSupplier()` to use:
    -   `city_subdivision_name`
    -   `country_identification_code`
-   Fixed duplicated inserts in certificate/credentials flow.
-   Fixed Compliance Certificate persistence.
-   Fixed Production Credentials persistence.
-   Replaced JSON certificate values with database values
    (`binary_security_token`, `secret`).
-   Fixed `company_tax_scheme` duplicate issue.
-   Fixed supplier `taxId` loading from database.
-   Successfully completed:
    1.  Generate CSR
    2.  Request Compliance Certificate
    3.  Run Compliance Check

## Current Database Status

The following tables are now the source of truth:

-   companies
-   company_party
-   company_address
-   company_tax_scheme
-   company_legal_entity
-   company_zatca_certificates
-   company_zatca_credentials

## Next Phase

### Goal

Completely stop using JSON files and migrate all ZATCA settings to the
database.

Target table:

`company_zatca_settings`

It stores:

-   environment
-   certificate_path
-   private_key_path
-   certificate_serial
-   zatca_client_id
-   zatca_secret
-   compliance_request_id
-   production_csid
-   production_secret
-   last_invoice_hash
-   last_invoice_uuid
-   last_icv
-   last_pih

## Migration Plan

1.  Search for every JSON read/write helper.
2.  Replace JSON reads with `company_zatca_settings`.
3.  Replace JSON writes with database updates.
4.  Create helper functions:
    -   getCompanyZatcaSettings()
    -   saveCompanyZatcaSettings()
    -   updateCompanyZatcaSettings()
5.  Keep Storage only for physical files:
    -   CSR
    -   Private Key
    -   Certificate
    -   XML
    -   QR
6.  Store only file paths and runtime values inside
    `company_zatca_settings`.
7.  Remove the remaining JSON dependency completely.

## Notes

-   Preserve compatibility with the Saleh7 library.
-   Avoid large refactoring.
-   Continue with incremental testing after each replacement.
