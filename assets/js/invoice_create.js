let itemsCache = [];
let invoiceAllowances = [];
let customerBox;
let invoiceKind;
document.addEventListener(
    "DOMContentLoaded",
    function(){
        const form = document.getElementById("invoiceCreateForm");
        const addItem = document.getElementById("addItem");
        invoiceKind=document.getElementById("invoiceKind");
        customerBox=document.getElementById("customerSection");
        customerSelect=document.getElementById("customerSelect");
        document
        .getElementById("addInvoiceAllowance")
        .addEventListener(
            "click",
            function(){
                addInvoiceAllowanceRow();
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
        invoiceKind.addEventListener("change",toggleCustomer);
        toggleCustomer();
        loadItems();
        if(typeof invoiceId !== "undefined" && invoiceId){
            loadInvoice(invoiceId);
        }            
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
                            <select class="form-select item-select">
                                <option value="">Select Item</option>
                            </select>
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
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>                                               
                    </tr>
                    `
                );
                fillItemList(
                    tbody.lastElementChild.querySelector(".item-select")
                );
                const row = tbody.lastElementChild;
                row.querySelector(".remove-item")
                .addEventListener(
                    "click",
                    function(){
                        row.remove();
                    }
                );                
            }
        );
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
                let invoiceType =
                    document
                    .getElementById(
                        "invoiceKind"
                    )
                    .value;
                    let invoiceData = {
                        invoiceNumber:document.getElementById("invoiceNumber").value,
                        invoiceType:{
                            invoice:invoiceType,
                            type:"invoice"
                        },
                        customerId:document.getElementById("customerSelect").value||null,
                        items:items
                    };
                    invoiceData.allowanceCharges = collectInvoiceAllowances();
                    if (invoiceType === "standard" && !invoiceData.customerId) {
                        showError("Please select customer for standard invoice.");                        
                        return;
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
    data.submit=submit;
    let url=window.APP.baseUrl+"/api/invoices/create.php";
    if(typeof invoiceId!=="undefined"&&invoiceId){
        data.invoiceId=invoiceId;
        url=window.APP.baseUrl+"/api/invoices/update.php";
    }
    console.log("invoiceId:",typeof invoiceId!=="undefined"?invoiceId:null);
    console.log("url:",url);
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
function loadInvoice(id){
    fetch(window.APP.baseUrl+"/api/invoices/view.php?mode=edit&id="+id)
    .then(response=>response.json())
    .then(result=>{
        if(!result.success){
            return;
        }
        const invoice=result.data;
        document.getElementById("invoiceNumber").value=invoice.invoice_number;
        document.getElementById("invoiceKind").value=invoice.invoice_kind;
        if(invoice.invoice_kind==="standard"){
            customerBox.style.display="block";
            loadCustomers().then(()=>{
                customerSelect.value=invoice.customer_id;
            });
        }        
        const tbody=document.getElementById("invoiceItems");
        tbody.innerHTML="";
        invoice.items.forEach(item=>{
            addInvoiceRow(item);
        });
    });
}
function addInvoiceRow(item){
    let tbody=document.getElementById("invoiceItems");
    const isPercent=Number(item.discount_percentage)>0;
    const discountValue=isPercent
        ? item.discount_percentage
        : (item.discount_amount ?? 0);

    tbody.insertAdjacentHTML(
        "beforeend",
        `
        <tr>
            <td>
                <select class="form-select item-select">
                    <option value="${item.item_id}">${item.item_name}</option>
                </select>
            </td>
            <td>
                <input type="number"
                    class="form-control item-qty"
                    value="${item.quantity}">
            </td>
            <td>
                <input type="number"
                    class="form-control item-price"
                    value="${item.unit_price}">
            </td>
            <td>
                <input type="number"
                    class="form-control item-discount"
                    value="${discountValue}">
            </td>
            <td>
                <select class="form-select item-discount-type">
                    <option value="amount" ${isPercent?"":"selected"}>
                        Amount
                    </option>
                    <option value="percent" ${isPercent?"selected":""}>
                        Percent
                    </option>
                </select>
            </td>
            <td>
                <input type="number"
                    class="form-control item-tax"
                    value="${item.tax_percent ?? 15}">
            </td>
            <td>
                <button type="button"
                    class="btn btn-danger btn-sm remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
        `
    );
    const row=tbody.lastElementChild;
    row.querySelector(".remove-item")
    .addEventListener(
        "click",
        function(){
            row.remove();
        }
    );
}
function addInvoiceAllowanceRow(data = {}) {
    const body = document.getElementById("invoiceAllowanceBody");
    body.insertAdjacentHTML(
        "beforeend",
        `
        <tr>
            <td>
                <select class="form-select ac-type">
                    <option value="0">Allowance</option>
                    <option value="1">Charge</option>
                </select>
            </td>
            <td>
                <input type="text"
                    class="form-control ac-reason">
            </td>
            <td>
                <select class="form-select ac-mode">
                    <option value="amount">Amount</option>
                    <option value="percent">Percent</option>
                </select>
            </td>
            <td>
                <input type="number"
                    class="form-control ac-value"
                    step="0.01"
                    value="0">
            </td>
            <td>
                <button type="button"
                    class="btn btn-danger btn-sm remove-ac">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
        `
    );
    const row = body.lastElementChild;
    row.querySelector(".ac-type").value =
        data.chargeIndicator ? "1" : "0";
    row.querySelector(".ac-reason").value =
        data.reason ?? "";
    row.querySelector(".ac-mode").value =
        data.mode ?? "amount";
    row.querySelector(".ac-value").value =
        data.value ?? 0;
    row.querySelector(".remove-ac")
        .addEventListener(
            "click",
            function () {
                row.remove();
            }
        );
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
        data.push({
            chargeIndicator:
                row.querySelector(".ac-type").value==="1",
            reasonCode:"95",
            reason:
                row.querySelector(".ac-reason").value||"Allowance",
            mode:
                row.querySelector(".ac-mode").value,
            value:value
        });
    });
    return data;
}
