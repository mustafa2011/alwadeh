
/**
 * ==========================================================
 * Components Library
 * ALWADEH ZATCA Portal v3
 * ==========================================================
 */

const Components = {

    /**
     * ----------------------------------------
     * Loading Button
     * ----------------------------------------
     */
    loadingButton(button, loading = true) {

        if (!button) return;

        if (loading) {

            if (!button.dataset.originalText) {
                button.dataset.originalText = button.innerHTML;
            }

            button.classList.add("btn-loading");

            button.disabled = true;

            button.innerHTML = `
                <span class="spinner-border spinner-border-sm"></span>
                Loading...
            `;

        } else {

            button.classList.remove("btn-loading");

            button.disabled = false;

            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
            }

        }

    },

    /**
     * ----------------------------------------
     * Empty State
     * ----------------------------------------
     */
    emptyState(message = "No Data Found") {

        return `
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No Records</h5>
                <p>${message}</p>
            </div>
        `;

    },

    /**
     * ----------------------------------------
     * Status Badge
     * ----------------------------------------
     */
    statusBadge(status, trueText = 'Completed', falseText = 'Pending') {

        return status
        ? `<span class="badge bg-success">${trueText}</span>`
        : `<span class="badge bg-secondary">${falseText}</span>`;

    },

    /**
     * ----------------------------------------
     * Confirm Dialog
     * ----------------------------------------
     */
    confirm(message = "Are you sure?") {

        return window.confirm(message);

    },

    /**
     * ----------------------------------------
     * Section Header
     * ----------------------------------------
     */
    sectionHeader(title, subtitle = "") {

        return `
            <div class="section-header">
                <div>
                    <h4>${title}</h4>
                    <p>${subtitle}</p>
                </div>
            </div>
        `;

    },

    /**
     * ----------------------------------------
     * Card Loading
     * ----------------------------------------
     */
    loading() {

        return `
            <div class="component-loading">
                <div class="spinner-border text-primary"></div>
            </div>
        `;

    }

};