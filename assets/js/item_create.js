document.addEventListener('DOMContentLoaded',()=>{
    document.getElementById('itemForm').addEventListener('submit',saveItem);
});
async function saveItem(e){
    e.preventDefault();
    const button=document.getElementById('btnSave');
    button.disabled=true;
    const data=getItemData();
    delete data.id;
    const response=await fetch('api/items/create.php',{
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
