document.addEventListener(
    "DOMContentLoaded",
    loadInvoice
);

function loadInvoice(){

    fetch(
        window.APP.baseUrl +
        "/api/invoices/view.php?id=" +
        window.invoiceId
    )

    .then(r => r.json())

    .then(result => {

        if(!result.success){

            document.getElementById(
                "invoiceInfo"
            ).innerHTML =
            `
            <tr>
                <td class="text-danger">
                    ${result.message}
                </td>
            </tr>
            `;

            return;
        }

        let invoice = result.data;

        document.getElementById(
            "invoiceInfo"
        ).innerHTML =
        `
        <tr>
            <th>Invoice Number</th>
            <td>${invoice.invoice_number}</td>
        </tr>

        <tr>
            <th>Type</th>
            <td>${invoice.invoice_kind}</td>
        </tr>

        <tr>
            <th>Status</th>
            <td>${invoice.invoice_status}</td>
        </tr>

        <tr>
            <th>Date</th>
            <td>${invoice.issue_date}</td>
        </tr>

        <tr>
            <th>Time</th>
            <td>${invoice.issue_time}</td>
        </tr>

        <tr>
            <th>ZATCA</th>
            <td>${invoice.clearance_status === "pending"
                ? invoice.reporting_status
                : invoice.clearance_status}</td>
        </tr>

        <tr>
            <th>UUID</th>
            <td>${invoice.invoice_uuid}</td>
        </tr>

        <tr>
            <th>ICV</th>
            <td>${invoice.icv}</td>
        </tr>
        `;

    })

    .catch(console.error);

}