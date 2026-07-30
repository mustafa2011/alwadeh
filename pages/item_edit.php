<?php
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid item.');
}
$isEdit = true;
include __DIR__ . '/item_form.php';