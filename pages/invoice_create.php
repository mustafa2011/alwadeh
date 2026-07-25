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
        <div id="invoiceAlert"></div>

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

            <div id="customerSection" style="display:none;">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Customer</label>
                        <select id="customerSelect" class="form-select">
                            <option value="">Select Customer</option>
                        </select>
                        </div>
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
                <button type="button" id="saveDraft" class="btn btn-secondary">
                    <i class="bi bi-save"></i>
                    Save Draft
                </button>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-send"></i>
                    Submit Invoice
                </button>
            </div>



        </form>


    </div>


</div>

<script> window.APP = {baseUrl: "<?= BASE_URL ?>"};</script>

<script src="<?= BASE_URL ?>/assets/js/invoice_create.js"></script>