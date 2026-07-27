document.addEventListener('DOMContentLoaded', () => {
    loadItems();
});
async function loadItems(){
    const response = await fetch('api/items/list.php');
    const result = await response.json();
    const tbody = document.getElementById('itemsTable');
    tbody.innerHTML='';
    if(result.data.length===0){
        tbody.innerHTML=`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    No items found.
                </td>
            </tr>`;
        return;
    }    
    if(!result.success){
        showError(result.message);
        return;
    }
    result.data.forEach(item=>{
        tbody.insertAdjacentHTML('beforeend',`
            <tr>
                <td>${item.item_code}</td>
                <td>${item.item_name}</td>
                <td>${item.category_name??''}</td>
                <td>${item.unit_name??''}</td>
                <td>${Number(item.selling_price).toFixed(4)}</td>
                <td>
                    ${item.status==1
                    ?'<span class="badge bg-success">Active</span>'
                    :'<span class="badge bg-secondary">Inactive</span>'}
                </td>
                <td>
                    <a href="?page=item_view&id=${item.id}"
                        class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="?page=item_edit&id=${item.id}"
                        class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button
                        class="btn btn-sm btn-danger"
                        onclick="deleteItem(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            `);
        });
}
async function deleteItem(id){
    if(!confirm('Delete this item?')){
        return;
    }
    const response=await fetch('api/items/delete.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id:id})
    });
    const result=await response.json();
    if(result.success){
        showSuccess(result.message);
        loadItems();
    }else{
        showError(result.message);
    }
}