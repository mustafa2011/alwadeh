console.log("ALWADEH ZATCA Loaded");

/**
 * Escape HTML special characters.
 *
 * @param {*} value
 * @returns {string}
 */
function escapeHtml(value) {

    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}

/**
 * Returns Bootstrap current badge.
 *
 * @param {boolean} current
 * @returns {string}
 */
function currentCompanyBadge(current) {

    if (!current) {
        return '';
    }

    return `
        <span class="badge bg-success">
            Current
        </span>
    `;

}

/**
 * Returns element by ID.
 *
 * @param {string} id
 * @returns {HTMLElement|null}
 */
function el(id) {
    return document.getElementById(id);
}

/**
 * Shows Bootstrap toast.
 */
function showToast(message, type = "success") {

    const container = document.getElementById("toastContainer");

    if (!container) {
        alert(message);
        return;
    }

    const id = "toast-" + Date.now();

    container.insertAdjacentHTML("beforeend", `
        <div id="${id}"
             class="toast align-items-center text-bg-${type} border-0 mb-2"
             role="alert">

            <div class="d-flex">

                <div class="toast-body">
                    ${escapeHtml(message)}
                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast">
                </button>

            </div>

        </div>
    `);

    const toastElement = document.getElementById(id);

    const toast = new bootstrap.Toast(toastElement, {
        delay: 3000
    });

    toast.show();

    toastElement.addEventListener("hidden.bs.toast", () => {
        toastElement.remove();
    });

}

function showSuccess(message) {
    showToast(message, "success");
}

function showError(message) {
    showToast(message, "danger");
}

function showWarning(message) {
    showToast(message, "warning");
}

function showInfo(message) {
    showToast(message, "info");
}