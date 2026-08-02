<?php
$isEdit = isset($_GET['id']);
$invoiceId = $_GET['id'] ?? null;
?>
<div class="row mb-4">
    <div class="col">
        <?= $isEdit ? 'Edit Draft Invoice' : 'Create Invoice' ?>
        <p class="page-subtitle">
            Invoice will stored in databse and will not send to ZATCA
        </p>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header">
        Invoice Information
    </div>
    <div class="card-body">
        <div id="invoiceAlert"></div>
        <form id="invoiceCreateForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Invoice Type
                    </label>
                    <select id="invoiceKind"
                            class="form-select">
                        <option value="simplified">
                            Simplified Invoice
                        </option>
                        <option value="standard">
                            Standard Invoice
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        Invoice Number
                    </label>
                    <input type="text"
                        id="invoiceNumber"
                        class="form-control"
                        value="<?= $isEdit ? '' : 'INV00001' ?>">
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
            <h5>
                Invoice Items
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>VAT %</th>
                    <th>Actions</th>
                </tr>
                    </thead>
                    <tbody id="invoiceItems">
                    </tbody>
                </table>
              
            </div>
            <button type="button"
                    id="addItem"
                    class="btn btn-outline-secondary mb-3">
                <i class="bi bi-plus"></i>
                Add Item
            </button>
            <hr>
            <h5>Invoice Allowances / Charges</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Mode</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="invoiceAllowanceBody">
                </tbody>
            </table>
            <button
                type="button"
                id="addInvoiceAllowance"
                class="btn btn-outline-primary mb-3">
                <i class="bi bi-plus"></i>
                Add Allowance / Charge
            </button>              
            <div class="d-flex justify-content-between">
                <a href="?page=invoices" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Back
                </a>
                <button type="submit" id="saveDraft" class="btn btn-secondary">
                    <i class="bi bi-save"></i>
                    Save for Submission
                </button>
            </div>

        </form>
    </div>
</div>
<script>window.invoiceId = <?= $invoiceId ?? 'null' ?>;</script>
<script> window.APP = {baseUrl: "<?= BASE_URL ?>"};</script>
<script src="<?= BASE_URL ?>/assets/js/invoice_create.js"></script>