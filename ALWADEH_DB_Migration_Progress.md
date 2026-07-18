# ALWADEH -- Progress Summary

## Completed

-   Migrated company lookup to database fields.
-   Fixed current company session handling.
-   Replaced certificate_settings.json persistence with DB persistence.
-   Added saveCertificateSettings().
-   Added getCertificateSettings().
-   Updated loadCertificateSettings() to read from
    company_zatca_settings.
-   Generate CSR now saves settings to DB.
-   Compliance completed successfully.

## Database

Extended company_zatca_settings with certificate settings fields.
Environment ENUM currently: - nonprod (default) - simulation -
production

## Next Session

1.  Remove remaining references to certificate_settings.json.
2.  Replace remaining JSON reads/writes with DB.
3.  Remove obsolete helper methods.
4.  End-to-end testing.
