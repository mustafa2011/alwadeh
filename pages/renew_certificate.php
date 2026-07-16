<?php
include '../includes/header.php';
?>

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Renew Production Certificate
        </h2>

        <p class="page-subtitle">
            Generate a new CSR and renew the current Production Certificate.
        </p>

    </div>

</div>

<div class="row page-section">

    <div class="col">

        <div class="card page-card shadow-sm">

            <div class="card-body">

                <div id="wizardMessage"></div>

                <div class="accordion" id="renewCertificateWizard">

                    <!-- STEP 1 -->

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button class="accordion-button"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#step1">

                                <strong>Step 1 :</strong>&nbsp; Generate Renewal CSR

                            </button>

                        </h2>

                        <div id="step1"
                             class="accordion-collapse collapse show">

                            <div class="accordion-body">

                                <form id="renewCSRForm">

                                    <p class="text-muted mb-4">

                                        A new CSR will be generated automatically using
                                        the current company information.

                                    </p>

                                    <div class="alert alert-info mb-4">

                                        <i class="bi bi-info-circle me-2"></i>

                                        No company information is required.
                                        Existing company and device information will be reused.

                                    </div>

                                    <button
                                        type="submit"
                                        id="generateRenewCSRBtn"
                                        class="btn btn-success">

                                        <i class="bi bi-check2-circle"></i>
                                        Generate Renewal CSR

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                    <!-- STEP 2 -->

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button class="accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#step2">

                                <strong>Step 2 :</strong>&nbsp; Renew Production Certificate

                            </button>

                        </h2>

                        <div id="step2"
                             class="accordion-collapse collapse">

                            <div class="accordion-body">

                                <form id="renewCertificateForm">

                                    <div class="row">

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                One-Time Password (OTP)

                                                <i class="bi bi-question-circle-fill text-primary"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="right"
                                                   title="Enter the 6-digit OTP generated from the ZATCA Portal.">
                                                </i>

                                            </label>

                                            <input type="text"
                                                   class="form-control"
                                                   id="renewOtp"
                                                   name="otp"
                                                   maxlength="6"
                                                   minlength="6"
                                                   pattern="[0-9]{6}"
                                                   inputmode="numeric"
                                                   autocomplete="one-time-code"
                                                   placeholder="Example: 123456"
                                                   required>

                                            <div class="form-text">

                                                Enter the OTP generated from the ZATCA Portal
                                                to renew your Production Certificate.

                                            </div>

                                        </div>

                                    </div>

                                    <hr>

                                    <button
                                        type="submit"
                                        id="renewCertificateBtn"
                                        class="btn btn-success">

                                        <i class="bi bi-arrow-repeat"></i>
                                        Renew Production Certificate

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="<?= BASE_URL ?>/assets/js/renew_certificate.js"></script>

<?php include '../includes/footer.php'; ?>