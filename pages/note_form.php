<form id="noteCreateForm">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Invoice Kind</label>
            <select id="invoiceKind" class="form-select">
                <option value="simplified">Simplified</option>
                <option value="standard">Standard</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Document Type</label>
            <select id="invoiceType" class="form-select">
                <option value="credit">Credit Note</option>
                <option value="debit">Debit Note</option>
            </select>
        </div>
    </div>
    <div id="originalInvoiceSection" style="display:none;">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Original Invoice</label>
                <select id="originalInvoiceSelect" class="form-select">
                    <option value="">Select Original Invoice</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Note Number</label>
                <input
                    type="text"
                    id="invoiceNumber"
                    class="form-control">
            </div>
        </div>
        <div id="settlementContainer" class="form-check mb-3 d-none">
            <input
                class="form-check-input"
                type="checkbox"
                id="settlementCheck">
            <label class="form-check-label" for="settlementCheck">
                Close Remaining Balance
            </label>
        </div>            
    </div>
    <div id="customerSection" style="display:none;">
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Customer</label>
                <select id="customerSelect" class="form-select">
                    <option value="">Select Customer</option>
                </select>
            </div>
        </div>
    </div>
    <hr>
    <h5>Items</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>VAT %</th>
            </tr>
        </thead>
        <tbody id="invoiceItems"></tbody>
    </table>
    <hr>
    <h5>Invoice Allowances / Charges</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Reason</th>
                <th>Mode</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody id="invoiceAllowanceBody"></tbody>
    </table>
    <hr>
    <div class="d-flex justify-content-between">
        <a href="?page=invoices" class="btn btn-outline-secondary">
            Back
        </a>
        <button
            type="submit"
            class="btn btn-warning">
            Create Note
        </button>
    </div>
</form>
<script>
window.invoiceId = null;
window.APP = {
    baseUrl: "<?= BASE_URL ?>"
};
</script>
<script src="<?= BASE_URL ?>/assets/js/note_create.js"></script>