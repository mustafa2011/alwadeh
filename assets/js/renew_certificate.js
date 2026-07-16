/**
 * Renew Production Certificate Module
 */
document.addEventListener('DOMContentLoaded', function () {

    initializeTooltips();

});

/**
 * Initialize Bootstrap Tooltips.
 */
function initializeTooltips()
{
    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );

    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Generate Renewal CSR
 */
document.getElementById('renewCSRForm').addEventListener('submit', function (e) {

    const form = this;

    if (!form) {
        return;
    }

    e.preventDefault();

    const formData = new FormData(form);

    const btn = document.getElementById('generateRenewCSRBtn');

    Components.loadingButton(btn, true);

    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Generating CSR...
    `;

    fetch('../api/generate_renew_csr.php', {

        method: 'POST',

        body: formData

    })
    .then(response => response.json())
    .then(result => {

        Components.loadingButton(btn, false);

        btn.innerHTML = `
            <i class="bi bi-check2-circle me-1"></i>
            Generate Renewal CSR
        `;

        if (result.success) {

            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.disabled = true;

            btn.innerHTML = `
                <i class="bi bi-check-circle-fill me-1"></i>
                CSR Generated
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
            Generate Renewal CSR
        `;

        showAlert(
            'wizardMessage',
            'danger',
            error
        );

    });

});

/**
 * Renew Production Certificate
 */
document.getElementById('renewCertificateForm').addEventListener('submit', function (e) {

    const form = this;

    if (!form) {
        return;
    }

    e.preventDefault();

    const formData = new FormData(form);

    const btn = document.getElementById('renewCertificateBtn');

    Components.loadingButton(btn, true);

    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Renewing...
    `;

    fetch('../api/renew_certificate.php', {

        method: 'POST',

        body: formData

    })
    .then(response => response.json())
    .then(result => {

        Components.loadingButton(btn, false);

        btn.innerHTML = `
            <i class="bi bi-arrow-repeat me-1"></i>
            Renew Production Certificate
        `;

        if (result.success) {

            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.disabled = true;

            btn.innerHTML = `
                <i class="bi bi-check-circle-fill me-1"></i>
                Certificate Renewed
            `;

            showAlert(
                'wizardMessage',
                'success',
                result.message
            );

        } else {

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
            <i class="bi bi-arrow-repeat me-1"></i>
            Renew Production Certificate
        `;

        showAlert(
            'wizardMessage',
            'danger',
            error
        );

    });

});

/**
 * Show Bootstrap alert.
 */
if (typeof showAlert !== 'function') {

    function showAlert(containerId, type, message)
    {
        document.getElementById(containerId).innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>
            </div>
        `;
    }

}

/**
 * Navigate Wizard Steps.
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