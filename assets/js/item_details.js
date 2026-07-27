document.addEventListener('DOMContentLoaded',()=>{
    loadItem();
});
async function loadItem(){
    const response=await fetch(`api/items/find.php?id=${itemId}`);
    const result=await response.json();
    if(!result.success){
        showError(result.message);
        return;
    }
    const item=result.data;
    document.getElementById('itemDetails').innerHTML=`
        <tr>
            <th>Code</th>
            <td>${item.item_code??''}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>${item.item_name??''}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>${item.item_type??''}</td>
        </tr>
        <tr>
            <th>Category</th>
            <td>${item.category_name??''}</td>
        </tr>
        <tr>
            <th>Unit</th>
            <td>${item.unit_name??''}</td>
        </tr>
        <tr>
            <th>Selling Price</th>
            <td>${Number(item.selling_price).toFixed(4)}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>${Number(item.status)===1?'Active':'Inactive'}</td>
        </tr>`;
}