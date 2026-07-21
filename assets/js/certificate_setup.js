/**
 * Certificate Module
 */
document.addEventListener('DOMContentLoaded', function () {

    const CertificateUI = {

        generateForm: el('generateCertificateForm'),
        requestForm: el('requestCertificateForm'),
        complianceForm: el('complianceForm'),
    
        generateButton: el('generateCertificateBtn'),
        requestButton: el('requestCertificateBtn'),
        complianceButton: el('complianceBtn'),
    
        wizardMessage: el('wizardMessage'),
    
        environment: el('environment'),
        commonName: el('common_name'),
        organization: el('organization'),
        organizationUnit: el('organizationUnit'),
        countryName: el('countryName'),
        otp: el('otp')
    
    };
    if (!CertificateUI.environment.value || !CertificateUI.commonName.value) {
        return;
    }
    
    updateCommonName(CertificateUI.commonName.value);
    initializeTooltips();

});

/**
 * Update Common Name.
 *
 * @param {string} value
 */
function updateCommonName(value) {

    switch (value) {

        case "nonprod":
            value = "TSTZATCA-Code-Signing";
            break;
    
        case "simulation":
            value = "PREZATCA-Code-Signing";
            break;
    
        case "production":
            value = "ZATCA-Code-Signing";
            break;
    
        default:
            value = "";
    }
}

/**
 * Update Common Name.
 */
function initializeTooltips(){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

}

/**
 * Generate Certificate
 */
document.getElementById('generateCertificateForm').addEventListener('submit', function(e){
    const form = this;
    
    if (!form) {
        return;
    }

    e.preventDefault();

    let formData = new FormData(this);
    
    const btn = document.getElementById('generateCertificateBtn');

    Components.loadingButton(btn, true);
    btn.innerHTML = 'Generating...';

    fetch('../api/generate_certificate.php',{

        method:'POST',

        body:formData

    })
    .then(async response => {
        const result = await response.json();
    
        if (!response.ok) {
            throw result;
        }
    
        return result;
    })
    .then(result => {

        Components.loadingButton(btn, false);

        btn.innerHTML = `
            <i class="bi bi-check2-circle me-1"></i>
            Generate Certificate
        `;
    
        if (result.success) {
            Components.loadingButton(btn, true);
            
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            
            btn.innerHTML = `
                <i class="bi bi-check-circle-fill me-1"></i>
                Certificate Generated
            `;       
            goToStep('step1', 'step2');
            setTimeout(function () {

                showAlert(
                    'wizardMessage',
                    'success',
                    result.message
                );
            
            }, 250);
    
        } else {
            Components.loadingButton(btn, false);
            
            btn.innerHTML = `
                <i class="bi bi-check-circle-fill me-1"></i>
                Certificate Generated
            `;       
    
            showAlert(
                'wizardMessage',
                'danger',
                result.message
            );
    
        }
    
    })
    .catch(error => {

        Components.loadingButton(btn, false);

        btn.innerHTML = `
            <i class="bi bi-check2-circle me-1"></i>
            Generate Certificate
        `;
        showAlert(
            'wizardMessage',
            'danger',
            error.message ?? 'Unexpected error.'
        );        
    
    });

});


/**
 * Request Certificate
 */
document.getElementById('requestCertificateForm').addEventListener('submit', function (e) {

    if (!document.getElementById("requestCertificateForm")) {
        return;
    }

    e.preventDefault();
    
    const form = this;
    
    const formData = new FormData(form);

    const btn = document.getElementById('requestCertificateBtn');

    Components.loadingButton(btn, true);
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Running Request Certificate...
    `;

    fetch('../Certificates/request_certificate.php', {

        method: 'POST',
        body: formData

    })
    .then(response => response.json())
    .then(result => {

        showAlert(
            'wizardMessage',
            result.success ? 'success' : 'danger',
            result.message
        );

        if (result.success) {

            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');

            btn.innerHTML = `
                <i class="bi bi-check-circle-fill me-1"></i>
                Request Compliance Certificate Completed
            `;

            goToStep('step2', 'step3');
        
        } else {

            Components.loadingButton(btn, false);

            btn.innerHTML = `
                <i class="bi bi-check2-circle me-1"></i>
                Request Compliance Certificate
            `;
        }

    })
    .catch(error => {

        showAlert(
            'wizardMessage',
            'danger',
            error
        );

        Components.loadingButton(btn, false);

        btn.innerHTML = `
            <i class="bi bi-check2-circle me-1"></i>
            Request Compliance Certificate
        `;


    });

});

/**
 * Compliance Check
 */
document.getElementById('complianceForm').addEventListener('submit', function (e) {

    if (!document.getElementById("complianceForm")) {
        return;
    }

    e.preventDefault();

    const btn = document.getElementById('complianceBtn');

    Components.loadingButton(btn, true);
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Running Compliance...
    `;

    fetch('../Certificates/compliance_check.php', {

        method: 'POST'

    })
    .then(response => response.json())
    .then(result => {

        showAlert(
            'wizardMessage',
            result.success ? 'success' : 'danger',
            result.message
        );

        if (result.success) {

            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');

            btn.innerHTML = `
                <i class="bi bi-check-circle-fill me-1"></i>
                Compliance Completed
            `;

            // جاهز لإضافة Step 4 مستقبلاً
            // goToStep('step3', 'step4');

        } else {

            Components.loadingButton(btn, false);

            btn.innerHTML = `
                <i class="bi bi-check2-circle me-1"></i>
                Run Compliance Check
            `;
        }

    })
    .catch(error => {

        showAlert(
            'wizardMessage',
            'danger',
            error
        );

        Components.loadingButton(btn, false);

        btn.innerHTML = `
            <i class="bi bi-check2-circle me-1"></i>
            Run Compliance Check
        `;


    });

});

/**
 * Show Bootstrap alert inside container.
 *
 * @param {string} containerId
 * @param {string} type
 * @param {string} message
 */
if (typeof showAlert !== 'function') {

    function showAlert(containerId, type, message)
    {
        document.getElementById(containerId).innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

}

/**
 * Navigate between wizard steps.
 *
 * @param {string} currentStepId
 * @param {string} nextStepId
 */
if (typeof goToStep !== 'function') {

    function goToStep(currentStepId, nextStepId)
    {
        bootstrap.Collapse
            .getOrCreateInstance(document.getElementById(currentStepId))
            .hide();

        bootstrap.Collapse
            .getOrCreateInstance(document.getElementById(nextStepId))
            .show();
    }

}

