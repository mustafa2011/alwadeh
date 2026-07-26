

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Customers
        </h2>

        <p class="page-subtitle">
            Manage ALWADEH ZATCA customers
        </p>

    </div>

</div>


<div class="row mb-3">

    <div class="col text-end">

        <a href="?page=customer_create"
           class="btn btn-primary">

            <i class="bi bi-plus-circle"></i>

            Create Customer

        </a>

    </div>

</div>



<div class="card shadow-sm">

    <div class="card-header">

        Customer List

    </div>


    <div class="card-body">


        <div class="table-responsive">
            <div id="customerAlert"></div>
            <table class="table table-striped align-middle">

                <thead>

                    <tr>
                        <th>
                            #
                        </th>
                        <th>
                            Customer Code
                        </th>
                        <th>
                            Registration Name
                        </th>
                        <th>
                            Customer Type
                        </th>
                        <th>
                            VAT Number
                        </th>
                        <th>
                            CRN
                        </th>
                        <th>
                            Country
                        </th>
                        <th>
                            Actions
                        </th>
                    </tr>

                </thead>


                <tbody id="customerTableBody">


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

<script src="<?= BASE_URL ?>/assets/js/customers.js"></script>

