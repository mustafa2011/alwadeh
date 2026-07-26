<?php

$id = (int)($_GET['id'] ?? 0);

?>

<div id="customerView"></div>

<script>
window.APP = {
    baseUrl: "<?= BASE_URL ?>",
    customerId: <?= $id ?>
};
</script>

<script src="<?= BASE_URL ?>/assets/js/customer_view.js"></script>