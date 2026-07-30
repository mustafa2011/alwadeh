<?php
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid invoice.');
}
$isEdit = true;
$invoiceId = $id;
include __DIR__ . '/invoice_form.php';