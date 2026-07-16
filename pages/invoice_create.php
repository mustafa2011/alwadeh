<?php
// require_once __DIR__ . '/../includes/api_bootstrap.php';
?>

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Create Invoice
        </h2>

        <p class="page-subtitle">
            Create and submit invoice to ZATCA
        </p>

    </div>

</div>



<div class="card shadow-sm">


    <div class="card-header">

        Invoice Information

    </div>


    <div class="card-body">


        <form id="invoiceCreateForm">


            <div class="row mb-3">


                <div class="col-md-6">

                    <label class="form-label">
                        Invoice Type
                    </label>


                    <select id="invoiceKind"
                            class="form-select">


                        <option value="simplified">
                            Simplified Invoice
                        </option>


                        <option value="standard">
                            Standard Invoice
                        </option>


                    </select>


                </div>



                <div class="col-md-6">


                    <label class="form-label">
                        Invoice Number
                    </label>


                    <input type="text"
                           id="invoiceNumber"
                           class="form-control"
                           value="INV00001">


                </div>


            </div>




            <hr>


            <h5>
                Customer
            </h5>


            <div class="row mb-3">


                <div class="col-md-6">


                    <label class="form-label">
                        Customer Name
                    </label>


                    <input type="text"
                           id="customerName"
                           class="form-control"
                           value="عميل تجريبي">


                </div>



                <div class="col-md-6">


                    <label class="form-label">
                        VAT Number
                    </label>


                    <input type="text"
                           id="customerVat"
                           class="form-control"
                           value="300000000000003">


                </div>


            </div>



            <hr>



            <h5>
                Invoice Items
            </h5>



            <div class="table-responsive">


                <table class="table table-bordered">


                    <thead>

                        <tr>

                            <th>
                                Item Name
                            </th>

                            <th>
                                Qty
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                VAT %
                            </th>


                        </tr>

                    </thead>



                    <tbody id="invoiceItems">


                        <tr>


                            <td>

                                <input type="text"
                                       class="form-control item-name"
                                       value="منتج تجريبي">

                            </td>



                            <td>

                                <input type="number"
                                       class="form-control item-qty"
                                       value="1">

                            </td>



                            <td>

                                <input type="number"
                                       class="form-control item-price"
                                       value="100">

                            </td>



                            <td>

                                <input type="number"
                                       class="form-control item-tax"
                                       value="15">

                            </td>



                        </tr>


                    </tbody>


                </table>


            </div>




            <button type="button"
                    id="addItem"
                    class="btn btn-outline-secondary mb-3">


                <i class="bi bi-plus"></i>

                Add Item


            </button>




            <div class="text-end">


                <button type="submit"
                        class="btn btn-success">


                    <i class="bi bi-send"></i>

                    Create Invoice


                </button>


            </div>



        </form>


    </div>


</div>

<script> window.APP = {baseUrl: "<?= BASE_URL ?>"};</script>

<script src="<?= BASE_URL ?>/assets/js/invoice_create.js"></script>