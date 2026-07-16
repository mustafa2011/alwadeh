

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Invoices
        </h2>

        <p class="page-subtitle">
            Manage ALWADEH ZATCA invoices
        </p>

    </div>

</div>


<div class="row mb-3">

    <div class="col text-end">

        <a href="?page=invoice_create"
           class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>

            Create Invoice

        </a>

    </div>

</div>



<div class="card shadow-sm">

    <div class="card-header">

        Invoice List

    </div>


    <div class="card-body">


        <div class="table-responsive">

            <table class="table table-striped align-middle">

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Invoice Number
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            ZATCA
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody id="invoiceTableBody">


                    <tr>

                        <td colspan="7"
                            class="text-center">

                            Loading...

                        </td>

                    </tr>


                </tbody>


            </table>

        </div>


    </div>

</div>


<script>
window.APP = {
    baseUrl: "<?= BASE_URL ?>"
};
</script>

<script src="<?= BASE_URL ?>/assets/js/invoices.js"></script>

