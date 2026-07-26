if (typeof showAlert !== 'function') {

    function showAlert(containerId,type,message)
    {
        document.getElementById(containerId).innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

}

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

            showAlert(
                'customerAlert',
                'danger',
                result.message
            );

            return;
        }

        showAlert(
            'customerAlert',
            'success',
            result.message
        );

        setTimeout(() => {
            window.location.href = `${APP.baseUrl}/?page=customers`;
        },1000);


    } catch(error){

        showAlert(
            'customerAlert',
            'danger',
            error.message
        );

    }
}