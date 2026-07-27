<div class="d-flex justify-content-between align-items-center mb-3">

    <h3>

        <i class="bi bi-box-seam"></i>

        <?= $isEdit ? 'Edit Item' : 'Create Item' ?>

    </h3>

    <a href="?page=items" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Back

    </a>

</div>

<div id="alertContainer"></div>

<form id="itemForm">

<input type="hidden"
id="item_id"
value="<?= $isEdit ? $id : '' ?>">

<div id="alertContainer"></div>

<div class="card shadow-sm">

    <div class="card-body">

        <form id="itemForm">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">Item Code</label>
                    <input
                        type="text"
                        class="form-control"
                        id="item_code"
                        >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Barcode</label>
                    <input
                        type="text"
                        class="form-control"
                        id="barcode">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Item Type</label>

                    <select
                        class="form-select"
                        id="item_type">

                        <option value="product">Product</option>
                        <option value="service">Service</option>

                    </select>

                </div>

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Item Name

                </label>

                <input
                    type="text"
                    class="form-control"
                    id="item_name"
                    >

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Description

                </label>

                <textarea
                    class="form-control"
                    id="description"
                    rows="3"></textarea>

            </div>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">

                        Category

                    </label>

                    <select
                        id="category_id"
                        class="form-select"
                        >

                    </select>

                </div>

                <div class="col-md-4 mb-3" id="unitGroup">

                    <label class="form-label">
                        Unit
                    </label>

                    <select
                        id="unit_id"
                        class="form-select"
                        >

                    </select>

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">

                        Tax Category

                    </label>

                    <select
                        id="tax_category_id"
                        class="form-select"
                        >

                    </select>

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Cost Price

                    </label>

                    <input
                        type="number"
                        step="0.0001"
                        value="0.0000"
                        id="cost_price"
                        class="form-control">

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Selling Price

                    </label>

                    <input
                        type="number"
                        step="0.0001"
                        value="0.0000"
                        id="selling_price"
                        class="form-control">

                </div>

            </div>

            <div class="form-check mb-2" id="inventoryGroup">

                <input
                    class="form-check-input"
                    type="checkbox"
                    id="track_inventory"
                    checked>

                <label class="form-check-label">

                    Track Inventory

                </label>

            </div>

            <div class="form-check mb-4">

                <input
                    class="form-check-input"
                    type="checkbox"
                    id="status"
                    checked>

                <label class="form-check-label">

                    Active

                </label>

            </div>

            <button class="btn btn-primary" id="btnSave">

                <i class="bi bi-check-circle"></i>

                Save Item

            </button>

        </form>

    </div>

</div>

<?php if ($isEdit): ?>

<script src="assets/js/item_form.js"></script>
<script src="assets/js/item_edit.js"></script>

<?php else: ?>

<script src="assets/js/item_form.js"></script>
<script src="assets/js/item_create.js"></script>

<?php endif; ?>