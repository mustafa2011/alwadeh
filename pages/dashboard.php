<div class="row mb-4">

    <div class="col">

    <h2 class="page-title">
            Dashboard
        </h2>
 
        <p class="page-subtitle">
            Welcome to ALWADEH ZATCA Portal
        </p>

    </div>

</div>


<div class="row g-4 page-section">

    <!-- Current Company -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    Current Company
                </div>

                <h5 id="dashboardCurrentCompany" class="card-value">
                    —
                </h5>

            </div>

        </div>

    </div>


    <!-- Environment -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    Environment
                </div>

                <h5 id="dashboardEnvironment">
                    —
                </h5>

            </div>

        </div>

    </div>

    <!-- Companies Counts -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    Companies Count
                </div>

                <h5 id="dashboardCompaniesCount">
                    0
                </h5>

            </div>

        </div>

    </div>

    <!-- Version -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    Version
                </div>

                <h5 id="dashboardCurrentVersion">
                    —
                </h5>

            </div>

        </div>

    </div>


    <!-- CSR Status -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    CSR Status
                </div>

                <div id="dashboardCSRStatus" class="dashboard-status">

                    <span class="badge bg-secondary">
                        Unknown
                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- Compliance Status -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    Compliance Status
                </div>

                <div id="dashboardComplianceStatus">

                    <span class="badge bg-secondary">
                        Unknown
                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- Production Certificate -->

    <div class="col-md-6 col-xl-3">

        <div class="card page-card dashboard-card shadow-sm h-100">

            <div class="card-body">

                <div class="card-title">
                    Production Certificate
                </div>

                <div id="dashboardProductionStatus">

                    <span class="badge bg-secondary">
                        Unknown
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


<div class="row mt-4">

    <div class="col-lg-12">

        <div class="card shadow-sm">

            <div class="card-header">
                Quick Actions
            </div>

            <div class="card-body">

                <div class="d-flex flex-wrap gap-2 quick-actions">

                    <a href="pages/companies.php" class="btn btn-primary">
                        <i class="bi bi-buildings"></i>

                        Companies

                    </a>

                    <a href="pages/certificate_setup.php"
                       class="btn btn-success">

                        <i class="bi bi-shield-check"></i>

                        Certificate Setup

                    </a>

                    <a href="pages/renew_certificate.php"
                       class="btn btn-success">

                        <i class="bi bi-arrow-repeat"></i>

                        Renew Certificate

                    </a>

                    <a href="?page=invoices"
                        class="btn btn-outline-primary">

                            <i class="bi bi-receipt"></i>
                            Invoices

                    </a>                    

                </div>

            </div>

        </div>

    </div>

</div>

<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
