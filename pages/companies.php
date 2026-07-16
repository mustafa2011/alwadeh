<?php
include '../includes/header.php';
require_once __DIR__ . '/../includes/api_bootstrap.php';
?>

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Companies
        </h2>

        <p class="page-subtitle">
            Manage registered companies and select the current working company.
        </p>

    </div>

</div>

<div class="row page-section">

    <div class="col">
        <div class="container py-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <button
                        id="btnRefresh"
                        class="btn btn-outline-secondary me-2">

                        Refresh

                    </button>

                    <button
                        id="btnAddCompany"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#companyModal">

                        Add Company

                    </button>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                            <tr>

                                <th>Company</th>

                                <th>CRN</th>

                                <th>VAT</th>

                                <th>Environment</th>

                                <th>CSR</th>

                                <th>Compliance</th>

                                <th>Production</th>

                                <th>Current</th>

                                <th width="220">
                                    Actions
                                </th>

                            </tr>

                            </thead>

                            <tbody id="companiesTableBody">

                            <tr>

                                <td colspan="9"
                                    class="text-center py-5">

                                    Loading...

                                </td>

                            </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        <!-- Company Modal -->

        <div class="modal fade"
            id="companyModal"
            tabindex="-1">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">

                            Company

                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <form id="companyForm">

                            <div id="companyFormAlert"></div>

                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Company Name

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="company_name"
                                        required>

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Branch Name

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="branch_name">

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        CRN

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="crn"
                                        required>

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        VAT

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="vat">

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        Environment

                                    </label>

                                    <select
                                        class="form-select"
                                        name="environment">

                                        <option value="nonprod">

                                            Sandbox

                                        </option>

                                        <option value="simulation">

                                            Simulation

                                        </option>

                                        <option value="production">

                                            Production

                                        </option>

                                    </select>

                                </div>

                            </div>

                        </form>

                    </div>

                    <div class="modal-footer">

                        <button
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Close

                        </button>

                        <button
                            type="submit"
                            form="companyForm"
                            id="btnSaveCompany"
                            class="btn btn-primary">
                        
                            Save
                        
                        </button>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/companies.js"></script>
<?php include '../includes/footer.php'; ?>