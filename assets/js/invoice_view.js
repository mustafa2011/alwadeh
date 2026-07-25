document.addEventListener(
    "DOMContentLoaded",
    loadInvoice
);

function loadInvoice() {

    fetch(
        window.APP.baseUrl +
        "/api/invoices/view.php?id=" +
        window.APP.invoiceId
    )
    .then(r => r.json())
    .then(result => {

        if (!result.success) {

            alert(result.message);

            return;

        }

        renderInvoice(result.data);

    });

}

function renderInvoice(invoice) {
    let html = `
    <div class="card shadow-sm mb-4">   
        <div class="card-header fw-bold">   
            Invoice Header    
        </div>    
        <div class="card-body">    
            <div class="row">    
                <div class="col-md-4 mb-3">    
                    <label class="text-muted small">
                        Invoice Number
                    </label>    
                    <div class="fw-semibold">
                        ${invoice.invoice_number}
                    </div>    
                </div>    
                <div class="col-md-4 mb-3">    
                    <label class="text-muted small">
                        UUID
                    </label>    
                    <div class="small text-break">
                        ${invoice.invoice_uuid}
                    </div>    
                </div>    
                <div class="col-md-4 mb-3">    
                    <label class="text-muted small">
                        Invoice Type
                    </label>    
                    <div class="fw-semibold text-capitalize">
                        ${invoice.invoice_kind}
                    </div>    
                </div>    
                <div class="col-md-3 mb-3">    
                    <label class="text-muted small">
                        Issue Date
                    </label>    
                    <div>
                        ${invoice.issue_date}
                    </div>    
                </div>    
                <div class="col-md-3 mb-3">    
                    <label class="text-muted small">
                        Issue Time
                    </label>    
                    <div>
                        ${invoice.issue_time}
                    </div>    
                </div>    
                <div class="col-md-3 mb-3">
                    <label class="text-muted small">
                        Currency
                    </label>    
                    <div>
                        ${invoice.currency_code}
                    </div>    
                </div>    
                <div class="col-md-3 mb-3">    
                    <label class="text-muted small">
                        ICV
                    </label>    
                    <div>
                        ${invoice.icv}
                    </div>    
                </div>
                    <div class="col-md-12">    
                    <label class="text-muted small">
                        Invoice Hash
                    </label>    
                    <div class="small text-break">
                        ${invoice.invoice_hash}
                    </div>    
                </div>    
            </div>    
        </div>    
    </div>
    
    `;
    html+=`
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            Supplier
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Company Name</label>
                    <div>${invoice.supplier.legal_entity?.registration_name??invoice.supplier.party_name??'-'}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="text-muted small">VAT Number</label>
                    <div>${invoice.supplier.tax?.vat_number??'-'}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="text-muted small">CR Number</label>
                    <div>${invoice.supplier.legal_entity?.company_id_value??'-'}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Street</label>
                    <div>${invoice.supplier.address?.street_name??'-'}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="text-muted small">Building</label>
                    <div>${invoice.supplier.address?.building_number??'-'}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="text-muted small">Postal Code</label>
                    <div>${invoice.supplier.address?.postal_zone??'-'}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">City</label>
                    <div>${invoice.supplier.address?.city_name??'-'}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Country</label>
                    <div>${invoice.supplier.address?.country_code??'-'}</div>
                </div>
            </div>
        </div>
    </div>
    `;
    if(invoice.customer){
        html+=`
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                Customer
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Customer Name</label>
                        <div>${invoice.customer.legal_entity?.registration_name??invoice.customer.party_name??'-'}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-muted small">VAT Number</label>
                        <div>${invoice.customer.tax?.vat_number??'-'}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-muted small">CR / ID</label>
                        <div>${invoice.customer.legal_entity?.company_id_value??'-'}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Street</label>
                        <div>${invoice.customer.address?.street_name??'-'}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-muted small">Building</label>
                        <div>${invoice.customer.address?.building_number??'-'}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-muted small">Postal Code</label>
                        <div>${invoice.customer.address?.postal_zone??'-'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">City</label>
                        <div>${invoice.customer.address?.city_name??'-'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Country</label>
                        <div>${invoice.customer.address?.country_code??'-'}</div>
                    </div>
                </div>
            </div>
        </div>
        `;
    } 

    html+=`
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            Invoice Details
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax %</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    invoice.items.forEach((item,index)=>{
        html+=`
                        <tr>
                            <td>${index+1}</td>
                            <td>${item.item_name}</td>
                            <td class="text-end">${item.quantity}</td>
                            <td class="text-end">${Number(item.unit_price).toFixed(2)}</td>
                            <td class="text-end">${Number(item.tax_percent).toFixed(2)}%</td>
                            <td class="text-end">${Number(item.tax_amount).toFixed(2)}</td>
                            <td class="text-end">${Number(item.payable_amount).toFixed(2)}</td>
                        </tr>
    `;
    });
    
    html+=`
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    `; 
    
    html+=`
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            Invoice Totals
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th>Line Extension Amount</th>
                                <td class="text-end">${Number(invoice.totals.line_extension_amount??0).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Tax Exclusive Amount</th>
                                <td class="text-end">${Number(invoice.totals.tax_exclusive_amount??0).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Tax Amount</th>
                                <td class="text-end">${Number(invoice.totals.tax_amount??0).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Tax Inclusive Amount</th>
                                <td class="text-end">${Number(invoice.totals.tax_inclusive_amount??0).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <th>Allowance Amount</th>
                                <td class="text-end">${Number(invoice.totals.allowance_total_amount??0).toFixed(2)}</td>
                            </tr>
                            <tr class="table-primary">
                                <th>Payable Amount</th>
                                <th class="text-end">${Number(invoice.totals.payable_amount??0).toFixed(2)} ${invoice.currency_code}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    `;
    
    html+=`
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            ZATCA Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Invoice Status</label>
                    <div>${invoice.invoice_status}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Submission Type</label>
                    <div>${invoice.clearance_status!=="pending"?"Clearance":"Reporting"}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">HTTP Status</label>
                    <div>${invoice.zatca_status_code??"-"}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Reporting Status</label>
                    <div>${invoice.reporting_status??"-"}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Clearance Status</label>
                    <div>${invoice.clearance_status??"-"}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small">Submitted At</label>
                    <div>${invoice.submitted_at??"-"}</div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Cleared At</label>
                    <div>${invoice.cleared_at??"-"}</div>
                </div>
            </div>
        </div>
    </div>
    `;
    
    html += `
    <div class="card shadow-sm">
        <div class="card-header fw-bold">
            Actions
        </div>
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="${window.APP.baseUrl}/api/invoices/view_xml.php?id=${invoice.id}"
               target="_blank"
               class="btn btn-outline-primary">
                View XML
            </a>
    
            <a href="${window.APP.baseUrl}/api/invoices/download_xml.php?id=${invoice.id}"
               class="btn btn-outline-primary">
                Download XML
            </a>
    
            <a href="${window.APP.baseUrl}/api/invoices/download_signed_xml.php?id=${invoice.id}"
               class="btn btn-outline-success">
                Download Signed XML
            </a>
    `;
    if(invoice.invoice_kind==="standard"){
        html+=`
            <a href="${window.APP.baseUrl}/api/invoices/download_cleared_xml.php?id=${invoice.id}" class="btn btn-outline-success">
                Download Cleared XML
            </a>
    `;
    }
    html+=`
            <a href="${window.APP.baseUrl}/api/invoices/download_pdf.php?id=${invoice.id}" class="btn btn-outline-danger">
                Download PDF
            </a>
    `;
    if(invoice.invoice_status==="draft"){
        html+=`
            <button id="submitDraftBtn" class="btn btn-primary">
                Submit to ZATCA
            </button>
            <a href="?page=invoice_create&id=${invoice.id}" class="btn btn-warning">
                Edit Draft
            </a>
    `;
    }
    html+=`
        </div>
    </div>
    `;

    document.getElementById("invoiceView").innerHTML = html;        
}

function viewXml(invoiceId){

    fetch(window.APP.baseUrl + "/api/invoices/view_xml.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            invoice_id: invoiceId
        })
    })
    .then(async (response) => {

        const text = await response.text();

        try{
            return JSON.parse(text);
        }catch(e){
            console.error(text);
            throw new Error("Invalid JSON response from server.");
        }

    })
    .then(result => {

        if(!result.success){

            showAlert(
                "invoiceAlert",
                "danger",
                result.message
            );

            return;
        }

        document
        .getElementById("viewXml")
        .addEventListener("click", function(){
        
            viewXml(
                document.getElementById("invoiceId").value
            );
        
        });

    })
    .catch(console.error);

}