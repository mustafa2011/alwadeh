document.addEventListener('DOMContentLoaded',()=>{
    loadItem();
    document.getElementById('itemForm').addEventListener('submit',updateItem);
});
async function loadItem(){
    const id=document.getElementById('item_id').value;
    const response=await fetch(`api/items/find.php?id=${id}`);
    const result=await response.json();
    if(!result.success){
        showError(result.message);
        return;
    }
    const item=result.data;
    document.getElementById('item_code').value=item.item_code??'';
    document.getElementById('barcode').value=item.barcode??'';
    document.getElementById('item_name').value=item.item_name??'';
    document.getElementById('description').value=item.description??'';
    document.getElementById('item_type').value=item.item_type??'product';
    document.getElementById('category_id').value=item.category_id??'';
    document.getElementById('unit_id').value=item.unit_id??'';
    document.getElementById('tax_category_id').value=item.tax_category_id??'';
    document.getElementById('cost_price').value=item.cost_price??0;
    document.getElementById('selling_price').value=item.selling_price??0;
    document.getElementById('track_inventory').checked=Number(item.track_inventory)===1;
    document.getElementById('status').checked=Number(item.status)===1;
    toggleItemType();
}
async function updateItem(e){
    e.preventDefault();
    const button=document.getElementById('btnSave');
    button.disabled=true;
    const data=getItemData();
    const response=await fetch('api/items/update.php',{
        method:'POST',
        headers:{
            'Content-Type':'application/json'
        },
        body:JSON.stringify(data)
    });
    const result=await response.json();
    button.disabled=false;
    if(result.success){
        showSuccess(result.message);
        setTimeout(()=>{
            window.location='?page=items';
        },600);
    }else{
        showError(result.message);
        return;
    }
}