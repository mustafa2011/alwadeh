document.addEventListener('DOMContentLoaded', loadCustomer);

async function loadCustomer() {

    const container = document.getElementById('customerView');

    container.innerHTML = 'Loading...';

    try {

        const response = await fetch(
            `${APP.baseUrl}/api/customers/view.php?id=${APP.customerId}`
        );

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const c = result.data;

        container.innerHTML = `
            <div class="card shadow-sm">

                <div class="card-header">
                    Customer Information
                </div>

                <div class="card-body">

                    <table class="table">

                        <tr>
                            <th width="220">Customer Code</th>
                            <td>${c.customer_code ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Customer Name</th>
                            <td>${c.customer_name}</td>
                        </tr>

                        <tr>
                            <th>Registration Name</th>
                            <td>${c.registration_name ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Type</th>
                            <td>${c.customer_type}</td>
                        </tr>

                        <tr>
                            <th>VAT Number</th>
                            <td>${c.vat_number ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Commercial Registration</th>
                            <td>${c.commercial_registration_number ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Street</th>
                            <td>${c.street_name ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Building Number</th>
                            <td>${c.building_number ?? ''}</td>
                        </tr>

                        <tr>
                            <th>City</th>
                            <td>${c.city_name ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Postal Code</th>
                            <td>${c.postal_zone ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Telephone</th>
                            <td>${c.telephone ?? ''}</td>
                        </tr>

                        <tr>
                            <th>Email</th>
                            <td>${c.electronic_mail ?? ''}</td>
                        </tr>

                    </table>

                </div>

            </div>
        `;

    } catch (e) {

        container.innerHTML = `
            <div class="alert alert-danger">
                ${e.message}
            </div>
        `;

    }

}