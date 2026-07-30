document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('customerForm');
    if (!form) {
        return;
    }
    form.addEventListener('submit', createCustomer);
});
async function createCustomer(e)
{
    e.preventDefault();
    const data = Object.fromEntries(
        new FormData(e.target).entries()
    );
    try {
        const response = await fetch(`${APP.baseUrl}/api/customers/create.php`, {
            method:'POST',
            headers:{
                'Content-Type':'application/json'
            },
            body:JSON.stringify(data)
        });
        const result = await response.json();

        if(!result.success){
            showError(result.message);
            return;
        }
        showSuccess(result.message);
        setTimeout(() => {
            window.location.href = `${APP.baseUrl}/?page=customers`;
        },1000);
    } catch(error){
        showError(result.message);
    }
}