<?php

require_once __DIR__.'/../vendor/autoload.php';

var_dump(class_exists(\App\Providers\Invoice\DatabaseInvoiceProvider::class));

$provider = new DatabaseInvoiceProvider($repository);

$data = $provider->getInvoice(7);

echo json_encode(
    $data,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);