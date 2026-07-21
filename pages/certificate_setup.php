<?php
include '../includes/header.php';
?>

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Certificate Setup
        </h2>

        <p class="page-subtitle">
            Generate CSR, run compliance checks and request the production certificate.
        </p>

    </div>

</div>

<div class="row page-section">

    <div class="col">

        <div class="card page-card shadow-sm">

        <div class="card-body">
        <div id="wizardMessage"></div>

        <!-- Wizard Header -->
        
        <div class="accordion" id="certificateWizard">

            <!-- STEP 1 -->
        
            <div class="accordion-item">
        
                <h2 class="accordion-header">
        
                    <button class="accordion-button"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#step1">
        
                        <strong>Step 1 :</strong>&nbsp; Generate Certificate
        
                    </button>
        
                </h2>
        
                <div id="step1"
                     class="accordion-collapse collapse show">
        
                    <div class="accordion-body">
        
                        <form id="generateCertificateForm" method="post">
                            
                            <!--Hidden nessecary inputs with default values-->
                            <input type="hidden" name="serial_1" value="Alwadeh">
                            <input type="hidden" name="serial_2" value="1.0">
                            <input type="hidden" id="common_name" name="common_name"> <!--value updated from function updateCommonName()-->
                            <input type="hidden" name="country_name" value="SA">
                            <input type="hidden" name="invoice_type" value="1100">

                            <div class="row">
                            
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Environment</label>
                                    <select class="form-select" name="environment" id="environment">
                                        <option value="nonprod">Sandbox</option>
                                        <option value="simulation">Simulation</option>
                                        <option value="production">Production</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        CR Number
                                        <i class="bi bi-question-circle-fill text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="right"
                                           title="CR Number consists of 10 digits">
                                        </i></label>
                                    <input class="form-control" type="text" name="crn" placeholder="Example: 1000000001" value="1000000001" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        VAT Number
                                        <i class="bi bi-question-circle-fill text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="right"
                                           title="VAT Number consists of 15 digits start/end with 3">
                                        </i>
                                    </label>
                                    <input class="form-control" type="text" name="organization_identifier" placeholder="Example: 399999999900003"  value="399999999900003" required>
                                </div>
 
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Organization Name</label>
                                    <input class="form-control" type="text" name="organization_name" placeholder="Example: Alwadeh IT Company" value="Alwadeh IT Company" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Organizational Unit Name</label>
                                    <input class="form-control" type="text" name="organizational_unit_name" placeholder="Example: Riyadh Branch" value="Riyadh Branch" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        National Short Address
                                        <i class="bi bi-question-circle-fill text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="right"
                                           title="National Short Address must contain 4 letters followed by 4 digits.">
                                        </i>
                                    </label>
                                    <input class="form-control" type="text" name="address" placeholder="Example: RHMA3184" value="RHMA3184" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Street</label>
                                    <input class="form-control" type="text" name="street" placeholder="Example: King Fahad Road" value="TST 1" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Building Number
                                        <i class="bi bi-question-circle-fill text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="right"
                                           title="Building number must match last 4 digint in National Short Address.">
                                        </i>
                                    </label>
                                    <input class="form-control" type="text" name="building_number"placeholder="Example: 3184" value="3184" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Subdivision</label>
                                    <input class="form-control" type="text" name="subdivision" placeholder="Example: Riyadh Branch" value="BR 1" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City</label>
                                    <input class="form-control" type="text" name="city" placeholder="Example: Riyadh" value="Riyadh" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Postal Code
                                            <i class="bi bi-question-circle-fill text-primary"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="right"
                                               title="Postal code must be exactly 5 digits.">
                                            </i>
                                    </label>
                                    <input class="form-control" type="text" name="postal_zone" placeholder="Example: 12345" value="12345" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Business Category</label>
                                    <input class="form-control" type="text" name="business_category" placeholder="Example: Trading" value="Trading" required>
                                </div>
                             </div>
                            

                            <div id="step1-part2"></div>
                            
                            <button
                                type="submit"
                                id="generateCertificateBtn"
                                class="btn btn-success">
                
                                <i class="bi bi-check2-circle"></i>
                                Generate Certificate
                
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
            
                        <strong>Step 2 :</strong>&nbsp; Request Compliance Certificate
            
                    </button>
            
                </h2>
            
                <div id="step2"
                     class="accordion-collapse collapse">
            
                    <div class="accordion-body">
            
                        <form id="requestCertificateForm">
            
                            <div class="row">

                                <div class="col-md-6">
                            
                                    <label class="form-label">
                                        One-Time Password (OTP)
                            
                                        <i class="bi bi-question-circle-fill text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="right"
                                           title="Enter the 6-digit OTP received from the ZATCA Portal after generating the CSR.">
                                        </i>
                            
                                    </label>
                            
                                    <input type="text"
                                           class="form-control"
                                           id="otp"
                                           name="otp"
                                           placeholder="Example: 123456"
                                           maxlength="6"
                                           minlength="6"
                                           pattern="[0-9]{6}"
                                           inputmode="numeric"
                                           autocomplete="one-time-code"
                                           value="123456"
                                           required>
                            
                                    <div class="form-text">
                                        Enter the <strong>6-digit OTP</strong> issued by ZATCA to request your Compliance Certificate.
                                    </div>
                            
                                </div>
                            
                            </div>
                            
                            <hr>
                            
                            <!--<button type="submit"-->
                            <!--        class="btn btn-success"-->
                            <!--        id="requestCertificateBtn">-->
                            
                            <!--    Request Compliance Certificate-->
                            
                            <!--</button>-->
                            <button
                                type="submit"
                                id="requestCertificateBtn"
                                class="btn btn-success">
                
                                <i class="bi bi-check2-circle"></i>
                                Request Compliance Certificate
                
                            </button>
            
                        </form>
            
                    </div>
            
                </div>
            
            </div>
        
        
            <!-- STEP 3 -->
        
            <div class="accordion-item">
        
                <h2 class="accordion-header">
        
                    <button class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#step3">
        
                        <strong>Step 3 :</strong>&nbsp; Compliance Check
        
                    </button>
        
                </h2>
        
                <div id="step3" class="accordion-collapse collapse">
                
                    <div class="accordion-body">
                
                        <form id="complianceForm">
                
                            <p class="text-muted mb-3">
                                Submit the six required compliance invoices to ZATCA.
                                If all invoices pass validation, a Production Certificate (PCSID)
                                will be requested automatically.
                            </p>
                
                            <button
                                type="submit"
                                id="complianceBtn"
                                class="btn btn-success">
                
                                <i class="bi bi-check2-circle"></i>
                                Run Compliance Check
                
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

<script src="<?= BASE_URL ?>/assets/js/certificate_setup.js"></script>
<?php include '../includes/footer.php'; ?>