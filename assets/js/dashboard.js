/**
 * ============================================================================
 * ALWADEH ZATCA
 * Dashboard Module
 * ----------------------------------------------------------------------------
 * Handles loading and rendering dashboard information.
 *
 * Responsibilities:
 *  - Load dashboard summary.
 *  - Populate dashboard cards.
 *  - Display current company information.
 *  - Display current environment.
 *  - Display certificate status.
 *
 * Dependencies:
 *  - api.js
 *  - app.js
 * ============================================================================
 */

/**
 * Loads dashboard data from the server.
 *
 * @async
 * @returns {Promise<void>}
 */
async function loadDashboard() {

    try {

        const result = await apiGet("dashboard.php");

        renderDashboard(result.data);

    }
    catch (error) {

        console.error(error);

        showError(error.message);

    }

}

/**
 * Renders dashboard information.
 *
 * @param {Object} data
 */
function renderDashboard(data) {

    if (!data) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Current Company
    |--------------------------------------------------------------------------
    */

    const currentCompany = el("dashboardCurrentCompany");

    if (currentCompany) {

        currentCompany.textContent =
            data.companyName || "No Company Selected";

    }

    /*
    |--------------------------------------------------------------------------
    | Current Version
    |--------------------------------------------------------------------------
    */

    const currentVersion = el("dashboardCurrentVersion");
    if (currentVersion) {

        currentVersion.textContent = data.currentVersion;

    }
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */

    const environment = el("dashboardEnvironment");

    if (environment) {

        environment.textContent =
            formatEnvironment(data.environment);

    }

    /*
    |--------------------------------------------------------------------------
    | Companies Count
    |--------------------------------------------------------------------------
    */

    const companiesCount = el("dashboardCompaniesCount");

    if (companiesCount) {

        companiesCount.textContent = data.companiesCount ?? 0;

    }

    /*
    |--------------------------------------------------------------------------
    | CSR Status
    |--------------------------------------------------------------------------
    */

    const csr = el("dashboardCSRStatus");

    if (csr) {

        csr.innerHTML = Components.statusBadge(data.status?.csr_generated);

    }

    /*
    |--------------------------------------------------------------------------
    | Compliance Status
    |--------------------------------------------------------------------------
    */

    const compliance = el("dashboardComplianceStatus");

    if (compliance) {

        compliance.innerHTML = Components.statusBadge(data.status?.compliance_certificate);

    }
        
    /*
    |--------------------------------------------------------------------------
    | Production Certificate
    |--------------------------------------------------------------------------
    */

    const production = el("dashboardProductionStatus");

    if (production) {

        production.innerHTML = Components.statusBadge(data.status?.production_certificate);

    }

}

/**
 * Formats environment name.
 *
 * @param {string|null} environment
 * @returns {string}
 */
function formatEnvironment(environment) {

    switch (environment) {

        case "sandbox":
        case "nonprod":
            return "Sandbox";

        case "simulation":
            return "Simulation";

        case "production":
            return "Production";

        default:
            return "Unknown";

    }

}

/**
 * Initializes dashboard page.
 */
document.addEventListener("DOMContentLoaded", () => {

    loadDashboard();

});