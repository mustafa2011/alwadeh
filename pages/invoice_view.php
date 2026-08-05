<?php
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        exit('Invalid invoice id.');
    }
?>

<div id="invoiceView"></div>

<div class="row mb-4">
    <div class="col text-end">
        <a href="?page=invoices"
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>
    </div>
</div>

<div id="invoiceAlert"></div>

<script>
    window.APP = {
        baseUrl: "<?= BASE_URL ?>",
        invoiceId: <?= (int)($_GET['id'] ?? 0) ?>
    };
</script>

<script src="<?= BASE_URL ?>/assets/js/invoice_view.js?v=2"></script>

