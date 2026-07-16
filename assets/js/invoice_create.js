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


                    invoiceNumber:
                    document.getElementById("invoiceNumber").value,


                    invoiceType:{

                        invoice: invoiceType,

                        type: "invoice"

                    },

                    buyer:{

                        name:
                        document
                        .getElementById(
                            "customerName"
                        )
                        .value,

                        vatNumber:
                        document
                        .getElementById(
                            "customerVat"
                        )
                        .value,

                        country:"SA",

                        city:"Riyadh",

                        street:"",

                        buildingNumber:"",

                        postalCode:""

                    },

                    items:items

                };

                createInvoice(
                    invoiceData
                );

            }
        );

    }
);

function createInvoice(data){
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
        // التعامل مع النتيجة
    })
    .catch(error => {
        console.error(error);
    });


}