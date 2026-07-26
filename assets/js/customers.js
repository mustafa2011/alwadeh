document.addEventListener('DOMContentLoaded', () => {
    loadCustomers();
});

async function loadCustomers() {
    const tbody = document.getElementById('customerTableBody');

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center">
                Loading...
            </td>
        </tr>
    `;

    try {

        const response = await fetch(`${APP.baseUrl}/api/customers/list.php`);

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const customers = result.data ?? [];

        if (customers.length === 0) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        No customers found.
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML = '';

        customers.forEach(customer => {

            tbody.insertAdjacentHTML('beforeend', `
                <tr>

                    <td>${customer.id}</td>

                    <td>${escapeHtml(customer.customer_code ?? '')}</td>

                    <td>${escapeHtml(customer.registration_name ?? customer.customer_name ?? '')}</td>

                    <td>${formatCustomerType(customer.customer_type)}</td>

                    <td>${escapeHtml(customer.vat_number ?? '')}</td>

                    <td>${escapeHtml(customer.commercial_registration_number ?? '')}</td>

                    <td>${escapeHtml(customer.country_code ?? '')}</td>

                    <td class="text-nowrap">

                        <a href="?page=customer_view&id=${customer.id}"
                        class="btn btn-sm btn-secondary">
                            View
                        </a>

                        <a href="?page=customer_edit&id=${customer.id}"
                        class="btn btn-sm btn-primary">
                            Edit
                        </a>

                        <button
                            class="btn btn-sm btn-danger"
                            onclick="deleteCustomer(${customer.id},'${escapeHtml(customer.registration_name ?? customer.customer_name ?? '')}')">
                            Delete
                        </button>

                    </td>

                </tr>
            `);

        });

    } catch (error) {

        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-danger text-center">
                    ${escapeHtml(error.message)}
                </td>
            </tr>
        `;

    }
}

async function deleteCustomer(id, name) {

    if (!confirm(`Delete "${name}" ?`)) {
        return;
    }

    try {

        const response = await fetch(`${APP.baseUrl}/api/customers/delete.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id
            })
        });

        const result = await response.json();

        showToast(result.message, result.success ? 'success' : 'danger');

        if (result.success) {
            loadCustomers();
        }

    } catch (error) {

        showToast(error.message,'danger');

    }

}

function formatCustomerType(type) {

    switch (type) {

        case 'company':
            return 'Company';

        case 'individual':
            return 'Individual';

        default:
            return '';

    }

}

function escapeHtml(value) {

    return String(value).replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[m]));

}