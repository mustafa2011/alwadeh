document.addEventListener(
    "DOMContentLoaded",
    function(){


        const form =
            document.getElementById(
                "invoiceCreateForm"
            );


        const addItem =
            document.getElementById(
                "addItem"
            );

            const invoiceKind=document.getElementById("invoiceKind");
            const customerBox=document.getElementById("customerSection");
            const customerSelect=document.getElementById("customerSelect");
            
            function toggleCustomer(){
                if(invoiceKind.value==="standard"){
                    customerBox.style.display="block";
                    loadCustomers();
                }else{
                    customerBox.style.display="none";
                    customerSelect.value="";
                }
            }
            
            invoiceKind.addEventListener("change",toggleCustomer);
            
            toggleCustomer();




            
        addItem.addEventListener(
            "click",
            function(){


                let tbody =
                    document.getElementById(
                        "invoiceItems"
                    );


                tbody.insertAdjacentHTML(
                    "beforeend",
                    `

                    <tr>


                        <td>

                            <input type="text"
                                   class="form-control item-name"
                                   value="منتج جديد">

                        </td>



                        <td>

                            <input type="number"
                                   class="form-control item-qty"
                                   value="1">

                        </td>



                        <td>

                            <input type="number"
                                   class="form-control item-price"
                                   value="0">

                        </td>



                        <td>

                            <input type="number"
                                   class="form-control item-tax"
                                   value="15">

                        </td>


                    </tr>

                    `
                );


            }
        );

        document
        .getElementById("saveDraft")
        .addEventListener("click", function () {    
            submitInvoice = false;
            form.requestSubmit();
        });

        form.addEventListener(
            "submit",
            function(e){
                e.preventDefault();
                let items = [];
                document
                .querySelectorAll(
                    "#invoiceItems tr"
                )
                .forEach(
                    function(row){
                        let name =
                            row.querySelector(
                                ".item-name"
                            ).value;
                        let qty =
                            parseFloat(
                                row.querySelector(
                                    ".item-qty"
                                ).value
                            );
                        let price =
                            parseFloat(
                                row.querySelector(
                                    ".item-price"
                                ).value
                            );
                        let tax =
                            parseFloat(
                                row.querySelector(
                                    ".item-tax"
                                ).value
                            );
                        items.push({
                            name:name,
                            quantity:qty,
                            unitPrice:price,
                            unitCode:"PCE",
                            allowanceCharges:[],
                            taxCategory:{
                                id:"S",
                                percent:tax
                            }
                        });
                    }
                );

                let invoiceType =
                    document
                    .getElementById(
                        "invoiceKind"
                    )
                    .value;
                    let invoiceData = {
                        invoiceNumber: document.getElementById("invoiceNumber").value,
                        invoiceType: {
                            invoice: invoiceType,
                            type: "invoice"
                        },
                        customerId: document.getElementById("customerSelect").value || null,
                        items: items
                    };

                    if (invoiceType === "standard" && !invoiceData.customerId) {
                        showAlert(
                            "invoiceAlert",
                            "danger",
                            "Please select customer for standard invoice."
                        );
                        return;
                    }
                    createInvoice(
                        invoiceData,
                        submitInvoice
                    );

                    submitInvoice = true;                    
                    // createInvoice(invoiceData);

            }
        );

    }
);

function createInvoice(data, submit = true){

    data.submit = submit;

    fetch(window.APP.baseUrl + "/api/invoices/create.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
    .then(async (response) => {
        const text = await response.text();

        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Server response:", text);
            throw new Error("Invalid JSON response from server.");
        }
    })
    .then(result => {
        showAlert(
            "invoiceAlert",
            result.success ? "success" : "danger",
            result.message
        );
    })
    .catch(error => {
        console.error(error);
    });

}

function loadCustomers(){
    customerSelect.innerHTML='<option value="">Select Customer</option>';
    fetch(window.APP.baseUrl+"/api/customers/list.php")
    .then(response=>response.json())
    .then(result=>{
        customerSelect.innerHTML='<option value="">Select Customer</option>';
        if(!result.success){
            return;
        }
        result.data.forEach(customer=>{
            customerSelect.innerHTML += `
            <option value="${customer.id}">
                ${customer.customer_name}
            </option>`;
        });
    });
}

function showAlert(containerId, type, message)
{
    document.getElementById(containerId).innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}