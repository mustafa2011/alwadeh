document.addEventListener('DOMContentLoaded', () => {

    loadCategories();
    loadUnits();
    loadTaxCategories();

    document
        .getElementById('item_type')
        .addEventListener('change', toggleItemType);

    toggleItemType();

});

async function loadCategories() {

    const response = await fetch('api/items/categories.php');

    const result = await response.json();

    if (!result.success) {

        showError(result.message);

        return;
    }

    const select = document.getElementById('category_id');

    select.innerHTML =
        '<option value="">Select Category</option>';

    result.data.forEach(category => {

        select.insertAdjacentHTML(
            'beforeend',
            `<option value="${category.id}">
                ${category.category_name}
            </option>`
        );

    });

}

async function loadUnits() {

    const response = await fetch('api/items/units.php');

    const result = await response.json();

    if (!result.success) {

        showError(result.message);

        return;
    }

    const select = document.getElementById('unit_id');

    select.innerHTML =
        '<option value="">Select Unit</option>';

    result.data.forEach(unit => {

        select.insertAdjacentHTML(
            'beforeend',
            `<option value="${unit.id}">
                ${unit.unit_name}
            </option>`
        );

    });

}

async function loadTaxCategories() {

    const response = await fetch('api/items/tax_categories.php');

    const result = await response.json();

    if (!result.success) {

        showError(result.message);

        return;
    }

    const select = document.getElementById('tax_category_id');

    select.innerHTML =
        '<option value="">Select Tax Category</option>';

    result.data.forEach(tax => {

        const label =
            tax.description
            ? `${tax.description} (${tax.tax_percent}%)`
            : `${tax.tax_percent}%`;

        select.insertAdjacentHTML(
            'beforeend',
            `<option value="${tax.id}">
                ${label}
            </option>`
        );

    });

}

function toggleItemType() {

    const isProduct =
        document.getElementById('item_type').value === 'product';

    document.getElementById('unitGroup').style.display =
        isProduct ? '' : 'none';

    document.getElementById('inventoryGroup').style.display =
        isProduct ? '' : 'none';

}

function getItemData() {

    return {

        id:
            document.getElementById('item_id').value,

        item_code:
            document.getElementById('item_code').value.trim(),

        barcode:
            document.getElementById('barcode').value.trim(),

        item_name:
            document.getElementById('item_name').value.trim(),

        description:
            document.getElementById('description').value.trim(),

        item_type:
            document.getElementById('item_type').value,

        category_id:
            document.getElementById('category_id').value,

        unit_id:
            document.getElementById('unit_id').value,

        tax_category_id:
            document.getElementById('tax_category_id').value,

        cost_price:
            document.getElementById('cost_price').value,

        selling_price:
            document.getElementById('selling_price').value,

        track_inventory:
            document.getElementById('track_inventory').checked
            ? 1
            : 0,

        status:
            document.getElementById('status').checked
            ? 1
            : 0

    };

}