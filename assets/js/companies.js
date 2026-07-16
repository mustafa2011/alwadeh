/**
 * Companies Module
 */

document.addEventListener('DOMContentLoaded', function () {
    
    const companyForm = document.getElementById('companyForm');
    const btnAddCompany = document.getElementById('btnAddCompany');
    const refreshButton = document.getElementById('btnRefresh');
    
    const companyModalElement = document.getElementById("companyModal");

    const companyModal = bootstrap.Modal.getOrCreateInstance(
        companyModalElement,
        {
            backdrop: "static",
            keyboard: false
        }
    );
    
    companyModalElement.addEventListener("hidden.bs.modal", function () {

        document.body.classList.remove("modal-open");
        document.body.style.removeProperty("padding-right");
    
        document.querySelectorAll(".modal-backdrop").forEach(function (backdrop) {
            backdrop.remove();
        });
    
    });

    loadCompanies();

    if (refreshButton) {

        refreshButton.addEventListener('click', function () {

            loadCompanies();

        });

    }

    btnAddCompany.addEventListener('click', () => {

        companyForm.reset();
        clearCompanyFormError();
        companyForm.elements["crn"].readOnly = false;
        companyModal.show();

    });

    companyForm.addEventListener('submit', async function (e) {

        e.preventDefault();
    
        const submitButton = companyForm.querySelector(
            'button[type="submit"]'
        );
    
        Components.loadingButton(submitButton, true);
    
        try {
    
            const formData = new FormData(companyForm);
    
            const response = await fetch('../api/company_save.php', {
                method: 'POST',
                body: formData,
                cache: 'no-store'
            });
    
            const result = await response.json();
    
            if (!result.success) {
                showCompanyFormError(result.message);
                return;
            }
    
            clearCompanyFormError();
    
            companyModal.hide();
    
            companyForm.reset();
    
            await loadCompanies();
    
        } catch (error) {
    
            console.error(error);
    
            showError("Unable to save company.");
    
        } finally {
    
            Components.loadingButton(submitButton, false);
    
        }
    
    });

    document.addEventListener("click", async (e) => {

        // ==========================
        // Edit Company
        // ==========================

        let button;
        
        button = e.target.closest(".btn-edit");

        if (button) {

            try {

                const response = await fetch(
                    "../api/company_details.php?crn=" +
                    encodeURIComponent(button.dataset.crn),
                    { cache: "no-store" }
                );

                const result = await response.json();

                if (!result.success) {
                    showCompanyFormError(result.message);
                    return;
                }

                const company = result.data;

                companyForm.elements["company_name"].value = company.company_name ?? "";
                companyForm.elements["branch_name"].value = company.branch_name ?? "";
                companyForm.elements["crn"].value = company.crn ?? "";
                companyForm.elements["vat"].value = company.vat ?? "";

                companyForm.elements["environment"].value =
                    company.environment ?? "nonprod";

                companyForm.elements["crn"].readOnly = true;
                clearCompanyFormError();
                companyModal.show();

            } catch (error) {

                console.error(error);
                alert("Unable to load company.");

            }

            return;
        }

        // ==========================
        // Switch Company
        // ==========================
        
        button = e.target.closest(".btn-switch");
        
        if (button) {
        
            try {
        
                const response = await fetch("../api/company_switch.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        crn: button.dataset.crn
                    })
                });
        
                const result = await response.json();
        
                if (!result.success) {
                    showError(result.message || "Unable to switch company.");
                    return;
                }
        
                loadCompanies();
        
            } catch (error) {
        
                console.error(error);
                showError("Unable to switch company.");
        
            }
        
            return;
        }
        
        // ==========================
        // Delete Company
        // ==========================
        
        button = e.target.closest(".btn-delete");
        
        if (button) {
        
            if (!Components.confirm(
                "Delete this company permanently?\n\nThis will remove all company data, certificates, logs and invoices."
            )) {
                return;
            }
        
            try {
        
                const response = await fetch("../api/company_delete.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        crn: button.dataset.crn
                    })
                });
        
                const result = await response.json();
        
                if (!result.success) {
                    showError(result.message);
                    return;
                }
                
                showSuccess(result.message);
                
                await loadCompanies();
        
            } catch (error) {
        
                console.error(error);
                showError("Unable to delete company.");
        
            }
        
            return;
        }
    });

});

/**
 * Load companies list.
 */
async function loadCompanies() {

    const tbody = el('companiesTableBody');

    if (tbody) {
        renderCompaniesMessage(Components.loading());
    }

    try {

        const response = await fetch('../api/company_list.php?_=' + Date.now(), {
            cache: 'no-store'
        });

        const result = await response.json();

        if (!result.success) {

            showCompanyFormError(result.message);

            if (tbody) {
                renderCompaniesMessage(
                    Components.emptyState(result.message)
                );
            }

            return;
        }

        renderCompanies(result.data);

    } catch (error) {

        console.error(error);

        if (tbody) {
            renderCompaniesMessage(
                Components.emptyState("Unable to load companies.")
            );
        }

        showToast('Unable to load companies.', 'danger');

    }

}
/**
 * Render companies table.
 */
function renderCompanies(companies) {

    const tbody = el('companiesTableBody');

    if (!tbody) {
        return;
    }

    tbody.innerHTML = '';

    if (companies.length === 0) {

        tbody.innerHTML = `
        <tr>
            <td colspan="9">
                ${Components.emptyState(
                    "No companies have been created yet."
                )}
            </td>
        </tr>
        `;

        return;
    }

    companies.forEach(company => {
        tbody.innerHTML += buildCompanyRow(company);
    });

}

/**
 * Builds company table row.
 *
 * @param {object} company
 * @returns {string}
 */
function buildCompanyRow(company) {

    return `
        <tr>

            <td>${escapeHtml(company.company_name)}</td>

            <td>${escapeHtml(company.crn)}</td>

            <td>${escapeHtml(company.vat)}</td>

            <td>${environmentLabel(company.environment)}</td>

            <td>
                ${Components.statusBadge(company.status?.csr_generated)}
            </td>

            <td>
                ${Components.statusBadge(company.status?.compliance_certificate)}
            </td>

            <td>
                ${Components.statusBadge(company.status?.production_certificate)}
            </td>

            <td>
                ${currentCompanyBadge(company.is_current)}
            </td>

            <td>
                ${buildActions(company)}
            </td>

        </tr>
    `;

}

/**
 * Builds company actions.
 *
 * @param {object} company
 * @returns {string}
 */
function buildActions(company) {

    let buttons = '';

    if (!company.is_current) {

        buttons += `
            <button
                class="btn btn-sm btn-success me-1 btn-switch"
                data-crn="${escapeHtml(company.crn)}">

                Select

            </button>
        `;

    }

    buttons += `
        <button
            class="btn btn-sm btn-primary me-1 btn-edit"
            data-crn="${escapeHtml(company.crn)}">

            Edit

        </button>

        <button
            class="btn btn-sm btn-danger btn-delete"
            data-crn="${escapeHtml(company.crn)}">

            Delete

        </button>
    `;

    return buttons;

}

/**
 * Returns environment display label.
 *
 * @param {string} environment
 * @returns {string}
 */
function environmentLabel(environment) {

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

function showCompanyFormError(message) {

    const alert = document.getElementById("companyFormAlert");

    alert.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

}

function clearCompanyFormError() {

    document.getElementById("companyFormAlert").innerHTML = "";

}

function renderCompaniesMessage(content) {

    const tbody = el('companiesTableBody');

    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="9">
                ${content}
            </td>
        </tr>
    `;

}