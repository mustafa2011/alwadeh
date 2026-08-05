let itemsCache = [];
let allowanceReasons = {
    allowances: [],
    charges: []
};
let customerBox;
let invoiceKind;
let invoiceType;
let originalInvoiceSelect;
let originalInvoiceSection;
let invoiceTypeSelect;
let originalInvoiceSource = null;
document.addEventListener(
    "DOMContentLoaded",
    function(){
        const form = document.getElementById("noteCreateForm");
        const addItem = document.getElementById("addItem");
        invoiceKind=document.getElementById("invoiceKind");
        invoiceType=document.getElementById("invoiceType");
        customerBox=document.getElementById("customerSection");
        customerSelect=document.getElementById("customerSelect");
        originalInvoiceSelect=document.getElementById("originalInvoiceSelect");
        originalInvoiceSection=document.getElementById("originalInvoiceSection");
        originalInvoiceSelect.addEventListener(
            "change",
            function(){
                if(this.value){
                    loadOriginalInvoiceData(this.value);
                }
            }
        );                        
        function toggleCustomer(){
            if(invoiceKind.value==="standard"){
                customerBox.style.display="block";
                loadCustomers();
            }else{
                customerBox.style.display="none";
                customerSelect.value="";
            }
        }
        invoiceKind.addEventListener(
            "change",
            function () {
                toggleCustomer();
                toggleOriginalInvoice();
            }
        );
        invoiceType.addEventListener(
            "change",
            function () {
                toggleOriginalInvoice();
            }
        );
        toggleCustomer();
        toggleOriginalInvoice();
        loadAllowanceReasons();
        if(typeof invoiceId !== "undefined" && invoiceId){
            loadInvoice(invoiceId);
        }            
        form.addEventListener("submit",function(e){
                e.preventDefault();
                submitInvoice = false;
                let items = [];
                document
                .querySelectorAll(
                    "#invoiceItems tr"
                )
                .forEach(
                    function(row){
                        let itemId =
                            parseInt(
                                row.querySelector(".item-select").value
                            );
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
                            itemId:itemId,
                            item_id:itemId,
                            itemName:row.querySelector(".item-select").selectedOptions[0].text,
                            quantity:qty,
                            unitPrice:price,
                            unitCode:"PCE",
                            taxCategory:{
                                id:"S",
                                percent:tax
                            }
                        });
                    }
                );
                const invoiceKind = document.getElementById("invoiceKind").value;
                const invoiceType = document.getElementById("invoiceType").value;
                let invoiceData = {
                    allowanceCharges: collectInvoiceAllowances(),
                    invoiceNumber:document.getElementById("invoiceNumber").value,
                    invoiceType: {
                        invoiceKind: invoiceKind,
                        invoiceType: invoiceType
                    },
                    customerId:document.getElementById("customerSelect").value||null,
                    originalInvoiceId:
                        document.getElementById("originalInvoiceSelect")?.value || null,
                    settlement:
                        document.getElementById("settlementCheck")?.checked || false,
                    items:items
                };
                if (invoiceKind === "standard" && !invoiceData.customerId) {
                    showError("Please select customer for standard invoice.");
                    return;
                }
                if (
                    (invoiceType === "credit" || invoiceType === "debit") &&
                    !invoiceData.originalInvoiceId
                ) {
                    showError("Please select original invoice.");
                    return;
                }               
                if(invoiceType==="credit"){
                    let changed=false;
                    document.querySelectorAll(".item-qty").forEach(function(input){
                        if(input.dataset.changed==="true"){
                            changed=true;
                        }
                    });
                    if(!changed){
                        showError("Please adjust item quantity before creating credit note.");
                        return;
                    }
                }
                createInvoice(
                    invoiceData,
                    submitInvoice
                );
                submitInvoice = true;                    
            }
        );
    }
);
function createInvoice(data, submit = true){
    data.submit = submit;
    let url = window.APP.baseUrl + "/api/notes/create.php";
    fetch(url,{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify(data)
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
        showSuccess(result.message);    
        if (result.success && !submit) {
            setTimeout(function () {
                window.location.href = "?page=invoices";
            }, 500);
        }
    })
    .catch(error => {
        console.error(error);
    });
}
function loadCustomers(){
    return fetch(window.APP.baseUrl+"/api/customers/list.php")
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
function loadItems(){
    fetch(window.APP.baseUrl + "/api/items/list.php")
    .then(response=>response.json())
    .then(result=>{
        if(!result.success){
            return;
        }
        itemsCache=result.data;
        fillItemLists();
    });
}
function fillItemLists(){
    document.querySelectorAll(".item-select").forEach(function(select){
        fillItemList(select);
    });
}
function fillItemList(select){
    select.innerHTML='<option value="">Select Item</option>';
    itemsCache.forEach(function(item){
        select.innerHTML+=`
        <option value="${item.id}">
            ${item.item_name}
        </option>`;
    });
    select.onchange=function(){
        itemChanged(this);
    };
}
function itemChanged(select){
    const row = select.closest("tr");
    const item = itemsCache.find(
        i => i.id == select.value
    );
    if(!item){
        return;
    }
    row.querySelector(".item-price").value =
        item.selling_price;
    row.querySelector(".item-tax").value =
        item.tax_percent;
}
function addInvoiceRow(item){
    let tbody=document.getElementById("invoiceItems");
    tbody.insertAdjacentHTML(
        "beforeend",
        `
        <tr>
            <td>
                <select class="form-select item-select" style="background-color:#e9ecef;opacity:1;">
                    <option value="${item.item_id ?? item.id}">${item.item_name}</option>
                </select>
            </td>
            <td>
                <input type="number"
                    class="form-control item-qty"
                    value="${item.remaining_quantity ?? item.quantity ?? 0}"
                    data-original-qty="${item.remaining_quantity ?? item.quantity ?? 0}"
                    step="0.001">
            </td>
            <td>
                <input type="number"
                    class="form-control item-price"
                    value="${item.unit_price}"
                    readonly style="background-color:#e9ecef;opacity:1;">
            </td>
            <td>
                <input type="number"
                    class="form-control item-tax"
                    value="${item.tax_percent ?? 15}"
                    readonly style="background-color:#e9ecef;opacity:1;">
            </td>
        </tr>
        `
    );
    const row=tbody.lastElementChild;   
    const qtyInput=row.querySelector(".item-qty");
    qtyInput.dataset.changed="false";
    qtyInput.addEventListener("input",function(){
        this.dataset.changed="true";
    });    
}
function toggleOriginalInvoice(){
    console.log("invoiceKind:", invoiceKind.value);
    console.log("invoiceType:", invoiceType.value);
    const isNote =
        invoiceType.value === "credit" ||
        invoiceType.value === "debit";
    originalInvoiceSection.style.display =
        isNote ? "block" : "none";
    if (!isNote) {
        originalInvoiceSelect.innerHTML =
            '<option value="">Select Original Invoice</option>';
        return;
    }
    loadOriginalInvoices();
}
function loadOriginalInvoices(){
    const endpoint =
        invoiceKind.value === "standard"
            ? "/api/invoices/cleared_list.php"
            : "/api/invoices/reported_list.php";
    fetch(window.APP.baseUrl + endpoint)
    .then(response => response.json())
    .then(result => {
        originalInvoiceSelect.innerHTML =
            '<option value="">Select Original Invoice</option>';

        if(!result.success){
            return;
        }

        result.data.forEach(function(invoice){
            let linesText = invoice.lines.map(line=>{
                return `${line.item_name}: ${line.remaining_quantity}`;
            }).join(", ");
        
            const closed =
                Number(invoice.remaining_amount) <= 0;
        
            originalInvoiceSelect.innerHTML += `
                <option 
                    value="${invoice.id}"
                    ${closed ? "disabled" : ""}
                >
                    ${invoice.invoice_number}
                    | Total: ${Number(invoice.payable_amount).toFixed(2)}
                    | Remaining: ${Number(invoice.remaining_amount).toFixed(2)}
                    ${closed ? "(Closed)" : ""}
                </option>
            `;
        });
    });
}
function loadOriginalInvoiceData(id){
    fetch(
        window.APP.baseUrl +
        "/api/invoices/credit_note_source.php?id=" + id
    )
    .then(response => response.json())
    .then(result => {
        if(!result.success){
            showError(result.message);
            return;
        }

        const invoice = result.data;
        originalInvoiceSource = invoice;
        const settlementContainer =
        document.getElementById("settlementContainer");
        const settlementCheck =
            document.getElementById("settlementCheck");
        settlementContainer.classList.add("d-none");
        settlementCheck.checked = false;
        const remainingAmount =
            parseFloat(invoice.remaining?.remaining_amount || 0);
        const remainingQty =
            invoice.items.reduce(
                (sum, item) =>
                    sum + parseFloat(item.remaining_quantity || 0),
                0
            );
        if (remainingQty === 0 && remainingAmount > 0) {
            settlementContainer.classList.remove("d-none");
        }
        document
        .getElementById("settlementCheck")
        .addEventListener("change", toggleSettlementMode);

        document.getElementById("invoiceItems").innerHTML="";

        if(
            invoice.invoice_kind === "standard" &&
            invoice.customer_id
        ){
            document.getElementById("customerSelect").value =
                invoice.customer_id;
        }

        invoice.items.forEach(function(item){
            addInvoiceRow(item);
        });
        document.querySelectorAll(".item-qty").forEach(function(input){
            input.addEventListener("input", recalculateCreditNotePreview);
        });        
        if(invoice.allowance_charges){
            document.getElementById("invoiceAllowanceBody").innerHTML="";
        
            invoice.allowance_charges.forEach(function(item){
                addInvoiceAllowanceRow(item);
            });
            document.querySelectorAll(
                "#invoiceAllowanceBody input, #invoiceAllowanceBody select"
            ).forEach(function(el){
                el.disabled = true;
            });
        }
        toggleSettlementMode();        
    });
}
function addInvoiceAllowanceRow(data = {}) {
    const body=document.getElementById("invoiceAllowanceBody");
    body.insertAdjacentHTML("beforeend",`
    <tr>
        <td>
            <select class="form-select ac-type" disabled>
                <option value="0">Allowance</option>
                <option value="1">Charge</option>
            </select>
        </td>
        <td>
            <select class="form-select ac-reason" disabled></select>
        </td>
        <td>
            <select class="form-select ac-mode" disabled>
                <option value="amount">Amount</option>
                <option value="percent">Percent</option>
            </select>
        </td>
        <td>
            <input class="form-control ac-value" type="number" step="0.01" readonly style="background-color:#e9ecef;opacity:1;">
        </td>
    </tr>
    `);
    const row=body.lastElementChild;
    row.querySelector(".ac-type").value=data.charge_indicator?"1":"0";
    fillAllowanceReasons(row);
    row.querySelector(".ac-reason").value=data.reason_code||"";
    row.querySelector(".ac-mode").value=
        Number(data.multiplier_factor)>0?"percent":"amount";
    const value=
        Number(data.multiplier_factor)>0
        ? data.multiplier_factor
        : data.amount;
    const valueInput=row.querySelector(".ac-value");
    valueInput.value=value;
    valueInput.dataset.originalValue=value;
}
function collectInvoiceAllowances(){
    let data=[];

    document.querySelectorAll("#invoiceAllowanceBody tr")
    .forEach(function(row){

        const value=parseFloat(
            row.querySelector(".ac-value").value
        )||0;

        if(value<=0){
            return;
        }

        const mode=row.querySelector(".ac-mode").value;

        data.push({
            chargeIndicator:
                row.querySelector(".ac-type").value==="1",
            reasonCode:
                row.querySelector(".ac-reason").value,
            reason:
                row.dataset.reasonText ||
                row.querySelector(".ac-reason").selectedOptions[0]?.text ||
                "",
            mode:mode,
            value:value,
            baseAmount:
                mode==="percent"
                    ? (parseFloat(row.dataset.baseAmount)||null)
                    : null
        });
    });

    return data;
}
function loadAllowanceReasons(){
    fetch(window.APP.baseUrl+"/api/settings/allowance_reasons.php")
    .then(response=>response.json())
    .then(result=>{
        if(!result.success){
            return;
        }
        allowanceReasons=result.data;
    });
}

function fillAllowanceReasons(row){
    const select=row.querySelector(".ac-reason");

    const isCharge=
        row.querySelector(".ac-type").value==="1";

    const list=isCharge
        ? allowanceReasons.charges
        : allowanceReasons.allowances;

    select.innerHTML="";

    list.forEach(function(reason){
        select.innerHTML+=`
        <option value="${reason.code}">
            ${reason.name_en}
        </option>`;
    });
}
function toggleSettlementMode() {
    const settlement =
        document.getElementById("settlementCheck").checked;
    document
        .querySelectorAll("#invoiceItems .item-qty")
        .forEach(el => {
            el.disabled = settlement;
        });
    document
        .querySelectorAll("#invoiceItems .item-price")
        .forEach(el => {
            el.disabled = settlement;
        });
}
function recalculateCreditNotePreview() {
    if(!originalInvoiceSource){
        return;
    }
    let originalQty = 0;
    let returnQty = 0;
    originalInvoiceSource.items.forEach(function(item){
        originalQty += parseFloat(item.quantity ?? item.original_quantity ?? 0);
    });
    document.querySelectorAll(".item-qty").forEach(function(input){
        returnQty += parseFloat(input.value || 0);
    });
    if(originalQty <= 0){
        return;
    }
    const ratio = returnQty / originalQty;
    document.querySelectorAll("#invoiceAllowanceBody tr").forEach(function(row){

        const valueInput = row.querySelector(".ac-value");
        const modeInput = row.querySelector(".ac-mode");
    
        if(!valueInput || !modeInput){
            return;
        }
    
        const originalValue = parseFloat(
            valueInput.dataset.originalValue || valueInput.value || 0
        );
    
        valueInput.dataset.originalValue = originalValue;
    
        if(modeInput.value === "amount"){
            valueInput.value = (originalValue * ratio).toFixed(2);
        }
    
    });
}