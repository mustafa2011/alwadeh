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
    class="btn btn-sm btn-outline-primary"
    title="View">
        <i class="bi bi-eye"></i>
    </a>

    ${
        invoice.invoice_status === 'signed'
        ?
        `
        <a href="?page=invoice_edit&id=${invoice.id}"
        class="btn btn-sm btn-outline-warning"
        title="Edit">
            <i class="bi bi-pencil"></i>
        </a>

        <button
            onclick="submitDraft(${invoice.id})"
            class="btn btn-sm btn-outline-success"
            title="Submit to ZATCA">
            <i class="bi bi-send"></i>
        </button>

        <a href="${window.APP.baseUrl}/api/invoices/download_xml.php?id=${invoice.id}"
        class="btn btn-sm btn-outline-success"
        title="Download XML">
            <i class="bi bi-download"></i>
        </a>

        <a href="${window.APP.baseUrl}/api/invoices/download_pdf.php?id=${invoice.id}"
        class="btn btn-sm btn-outline-danger"
        title="Download PDF">
            <i class="bi bi-file-earmark-pdf"></i>
        </a>
        `
        :
        `
        <a href="${window.APP.baseUrl}/api/invoices/download_xml.php?id=${invoice.id}"
        class="btn btn-sm btn-outline-success"
        title="Download XML">
            <i class="bi bi-download"></i>
        </a>

        <a href="${window.APP.baseUrl}/api/invoices/download_pdf.php?id=${invoice.id}"
        class="btn btn-sm btn-outline-danger"
        title="Download PDF">
            <i class="bi bi-file-earmark-pdf"></i>
        </a>
        `
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
    if(!confirm("Submit this invoice to ZATCA?")){
        return;
    }

    fetch(window.APP.baseUrl+"/api/invoices/submit_draft.php",{
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
    .catch(console.error);
}