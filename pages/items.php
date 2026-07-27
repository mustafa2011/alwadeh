<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-box-seam"></i>
        Items
    </h3>
    <a href="?page=item_create"
       class="btn btn-primary">
        <i class="bi bi-plus-circle"></i>
        New Item
    </a>
</div>
<div id="alertContainer"></div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th width="170">Actions</th>
                </tr>
                </thead>
                <tbody id="itemsTable"></tbody>
            </table>
        </div>
    </div>
</div>
<script src="assets/js/item_view.js"></script>