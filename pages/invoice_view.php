<?php

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid invoice id.');
}

?>

<div class="row mb-4">

    <div class="col">

        <h2 class="page-title">
            Invoice Details
        </h2>

        <p class="page-subtitle">
            View invoice information
        </p>

    </div>

    <div class="col text-end">

        <a href="?page=invoices"
           class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Back

        </a>

    </div>

</div>

<div id="invoiceAlert"></div>

<div class="card shadow-sm">

    <div class="card-header">

        Invoice

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <table class="table">

                    <tbody id="invoiceInfo">

                        <tr>

                            <td class="text-center">

                                Loading...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script>

window.APP = {
    baseUrl: "<?= BASE_URL ?>"
};

window.invoiceId = <?= $id ?>;

</script>

<script src="<?= BASE_URL ?>/assets/js/invoice_view.js"></script>