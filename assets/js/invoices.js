document.addEventListener(
    "DOMContentLoaded",
    function(){

        loadInvoices();

    }
);



function loadInvoices(){

    fetch(window.APP.baseUrl + "/api/invoices/list.php")
    .then(response => response.json())

    .then(result => {


        let tbody =
            document.getElementById(
                "invoiceTableBody"
            );


        tbody.innerHTML = "";


        if(!result.success){

            tbody.innerHTML =
            `
            <tr>
                <td colspan="7"
                    class="text-center text-danger">

                    ${result.message}

                </td>
            </tr>
            `;

            return;

        }



        if(result.data.length === 0){

            tbody.innerHTML =
            `
            <tr>
                <td colspan="7"
                    class="text-center">

                    No invoices found

                </td>
            </tr>
            `;

            return;

        }



        result.data.forEach(
            function(invoice,index){


                tbody.innerHTML +=
                `

                <tr>

                    <td>
                        ${index+1}
                    </td>


                    <td>
                        ${invoice.invoice_number}
                    </td>


                    <td>
                        ${invoice.invoice_kind}
                    </td>

                    <td>
                        ${invoice.issue_date}
                    </td>

                    <td>
                        <span class="badge bg-secondary">
                            ${invoice.invoice_status}
                        </span>
                    </td>

                    <td>
                        ${invoice.zatca_status ?? '-'}
                    </td>
                        
                    <td>
                        ${invoice.updated_at ?? '-'}
                    </td>


                    <td>

                        <a href="?page=invoice_view&id=${invoice.id}"
                        class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                        
                        ${
                            invoice.invoice_status === 'draft'
                            ?
                            `
                            <button
                                onclick="submitDraft(${invoice.id})"
                                class="btn btn-sm btn-outline-success">
                                Submit Draft
                            </button>
                            `
                            :
                            ''
                        }

                    </td>


                </tr>


                `;


            }
        );


    })

    .catch(error=>{

        console.error(error);

    });


}

function submitDraft(invoiceId){

    if(!confirm(
        "This invoice is saved as draft.\n\nDo you want to submit it to ZATCA?"
    )){
        return;
    }

    fetch(window.APP.baseUrl + "/api/invoices/submit_draft.php", {
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            invoiceId:invoiceId
        })
    })
    .then(response=>response.json())
    .then(result=>{

        alert(result.message);

        if(result.success){
            loadInvoices();
        }

    })
    .catch(error=>{
        console.error(error);
    });

}