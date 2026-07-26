document.addEventListener('DOMContentLoaded', () => {
    loadCustomer();
    document
        .getElementById('customerEditForm')
        .addEventListener('submit', updateCustomer);
});

async function loadCustomer() {

    try {

        const response = await fetch(
            `${APP.baseUrl}/api/customers/view.php?id=${APP.customerId}`
        );

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const c = result.data;

        setValue('id', c.id);
        setValue('customer_code', c.customer_code);
        setValue('customer_name', c.customer_name);
        setValue('registration_name', c.registration_name);
        setValue('customer_type', c.customer_type);
        setValue('customer_type_hidden', c.customer_type);
        setValue('vat_number', c.vat_number);
        setValue('commercial_registration_number', c.commercial_registration_number);
        setValue('street_name', c.street_name);
        setValue('building_number', c.building_number);
        setValue('city_name', c.city_name);
        setValue('postal_zone', c.postal_zone);
        setValue('country_subentity', c.country_subentity);
        setValue('additional_number', c.additional_number);
        setValue('country_code', c.country_code ?? 'SA');
        setValue('currency_code', c.currency_code ?? 'SAR');
        setValue('payment_terms', c.payment_terms);
        setValue('credit_limit', c.credit_limit);
        setValue('contact_name', c.contact_name);
        setValue('telephone', c.telephone);
        setValue('electronic_mail', c.electronic_mail);

        toggleCustomerType();

    } catch (error) {
        showError(result.message);
    }

}

async function updateCustomer(e) {

    e.preventDefault();

    const data = Object.fromEntries(
        new FormData(e.target).entries()
    );

    try {

        const response = await fetch(
            `${APP.baseUrl}/api/customers/update.php`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }
        );

        const result = await response.json();

        if (!result.success) {

            showError(result.message);

            return;

        }

        showSuccess(result.message);
        
        setTimeout(() => {

            window.location.href =
                `${APP.baseUrl}/?page=customers`;

        }, 1000);

    } catch (error) {
        showError(result.message);
    }

}

function setValue(id, value) {

    const element = document.getElementById(id);

    if (!element) {
        return;
    }

    element.value = value ?? '';

}

function toggleCustomerType() {

    const customerType = document.getElementById('customer_type');

    if (!customerType) {
        return;
    }

    const isCompany = customerType.value === 'company';

    const companySections = document.getElementById('companySections');
    
    if (companySections) {
        companySections.style.display = isCompany ? '' : 'none';
    }

    const vat = document.getElementById('vat_number');
    const crn = document.getElementById('commercial_registration_number');

    if (vat) {
        vat.required = isCompany;
    }

    if (crn) {
        crn.required = isCompany;
    }

}