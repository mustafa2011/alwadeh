<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Create Customer
        </h2>

        <p class="page-subtitle">
            Create a new customer
        </p>

    </div>

</div>

<div class="card shadow-sm">

    <div class="card-body">

        <div id="customerAlert"></div>

        <form id="customerForm">
            
            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Customer Code
                    </label>

                    <input
                        type="text"
                        name="customer_code"
                        class="form-control">

                </div>

                <div class="col-md-8 mb-3">

                    <label class="form-label">
                        Customer Name
                    </label>

                    <input
                        type="text"
                        name="customer_name"
                        class="form-control"
                        >

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Registration Name
                    </label>

                    <input
                        type="text"
                        name="registration_name"
                        class="form-control"
                        >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Customer Type
                    </label>

                    <select
                        name="customer_type"
                        id="customer_type"
                        class="form-select"
                        >

                        <option value="company">
                            Company
                        </option>

                        <option value="individual">
                            Individual
                        </option>

                    </select>

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3" id="vatNumberGroup">

                    <label class="form-label">
                        VAT Number
                    </label>

                    <input
                        type="text"
                        id="vat_number"
                        name="vat_number"
                        class="form-control"
                        maxlength="15">

                </div>

                <div class="col-md-6 mb-3" id="crnGroup">

                    <label class="form-label">
                        Commercial Registration Number
                    </label>

                    <input
                        type="text"
                        id="commercial_registration_number"
                        name="commercial_registration_number"
                        class="form-control"
                        maxlength="10">

                </div>

            </div>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Country
                    </label>

                    <input
                        type="text"
                        name="country_code"
                        value="SA"
                        class="form-control"
                        maxlength="2"
                        >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Currency
                    </label>

                    <input
                        type="text"
                        name="currency_code"
                        value="SAR"
                        class="form-control"
                        maxlength="3"
                        >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Credit Limit
                    </label>

                    <input
                        type="number"
                        name="credit_limit"
                        value="0"
                        step="0.01"
                        class="form-control">

                </div>

            </div>

            <div class="row">

                <div class="col-md-12 mb-3">

                    <label class="form-label">
                        Payment Terms
                    </label>

                    <input
                        type="text"
                        name="payment_terms"
                        class="form-control">

                </div>

            </div>

            <hr>

            <h5 class="mb-3">
                Address
            </h5>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Street Name
                    </label>

                    <input
                        type="text"
                        name="street_name"
                        class="form-control"
                        >

                </div>

                <div class="col-md-3 mb-3">

                    <label class="form-label">
                        Building Number
                    </label>

                    <input
                        type="text"
                        name="building_number"
                        class="form-control"
                        >

                </div>

                <div class="col-md-3 mb-3">

                    <label class="form-label">
                        Additional Number
                    </label>

                    <input
                        type="text"
                        name="additional_number"
                        class="form-control">

                </div>

            </div>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        City
                    </label>

                    <input
                        type="text"
                        name="city_name"
                        class="form-control"
                        >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        State
                    </label>

                    <input
                        type="text"
                        name="country_subentity"
                        class="form-control"
                        >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Postal Code
                    </label>

                    <input
                        type="text"
                        name="postal_zone"
                        class="form-control"
                        >

                </div>

            </div>

            <hr>

            <h5 class="mb-3">
                Contact
            </h5>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Contact Name
                    </label>

                    <input
                        type="text"
                        name="contact_name"
                        class="form-control">

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Telephone
                    </label>

                    <input
                        type="text"
                        name="telephone"
                        class="form-control">

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="electronic_mail"
                        class="form-control">

                </div>

            </div>

            <div class="row">

                <div class="col-12">

                    <a href="?page=customers" class="btn btn-secondary">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        Save Customer

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<script>
window.APP = {
    baseUrl: "<?= BASE_URL ?>"
};
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const customerType = document.getElementById('customer_type');

    if (!customerType) {
        return;
    }

    const vatGroup = document.getElementById('vatNumberGroup');
    const crnGroup = document.getElementById('crnGroup');

    const vat = document.getElementById('vat_number');
    const crn = document.getElementById('commercial_registration_number');

    function toggleCustomerType() {

        if (customerType.value === 'company') {

            if(vatGroup) vatGroup.style.display = '';
            if(crnGroup) crnGroup.style.display = '';

        } else {

            if(vatGroup) vatGroup.style.display = '';
            if(crnGroup) crnGroup.style.display = 'none';

            if(vat) {
                vat.value = '';
            }

            if(crn) {
                crn.value = '';
            }

        }

    }

    toggleCustomerType();

    customerType.addEventListener('change', toggleCustomerType);

});
</script>

<script src="<?= BASE_URL ?>/assets/js/customer_create.js"></script>