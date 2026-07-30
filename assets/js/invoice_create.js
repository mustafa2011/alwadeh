let itemsCache = [];
let customerBox;
let invoiceKind;
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
            invoiceKind=document.getElementById("invoiceKind");
            customerBox=document.getElementById("customerSection");
            customerSelect=document.getElementById("customerSelect");          
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
                                class="form-control item-discount"
                                value="0"
                                min="0"
                                step="0.01">
                        </td>
                        <td>
                            <select class="form-select item-discount-type">
                                <option value="amount">Amount</option>
                                <option value="percent">Percent</option>
                            </select>
                        </td>
                        <td>
                            <input type="number"
                                class="form-control item-tax"
                                value="15">
                        </td>
                    </tr>
                    `
                );
                fillItemList(
                    tbody.lastElementChild.querySelector(".item-select")
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
                        let discount =
                            parseFloat(
                                row.querySelector(
                                    ".item-discount"
                                ).value
                            );
                        let discountType =
                            row.querySelector(
                                ".item-discount-type"
                            ).value;
                        let discountAmount=discount;
                        if(discountType==="percent"){
                            discountAmount=(qty*price)*(discount/100);
                        }                            
                        items.push({
                            itemId:itemId,
                            quantity:qty,
                            unitPrice:price,
                            unitCode:"PCE",
                            discount:{
                                type:discountType,
                                value:discount,
                                amount:discountAmount,
                                reason:"Discount"
                            },                                                        
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
    const row=select.closest("tr");
    const item=itemsCache.find(i=>i.id==select.value);
    if(!item){
        return;
    }
    row.querySelector(".item-price").value=item.selling_price;
    row.querySelector(".item-tax").value=item.tax_percent;
    row.querySelector(".item-discount").value = item.discount_amount ?? 0;
    row.querySelector(".item-discount-type").value =
        (Number(item.discount_percentage) > 0)
            ? "percent"
            : "amount";    
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
                <input type="number" class="form-control item-qty" value="${item.quantity}">
            </td>
            <td>
                <input type="number" class="form-control item-price" value="${item.unit_price}">
            </td>
            <td>
                <input type="number" class="form-control item-discount" value="${discountValue}">
            </td>
            <td>
                <select class="form-select item-discount-type">
                    <option value="amount" ${isPercent?"":"selected"}>Amount</option>
                    <option value="percent" ${isPercent?"selected":""}>Percent</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control item-tax" value="${item.tax_percent ?? 15}">
            </td>
        </tr>
        `
    );
}