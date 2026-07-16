<?php
require_once __DIR__.'/config.php';

$currentUser = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title><?= APP_NAME ?></title>


    <link rel="icon" type="image/gif" href="<?= BASE_URL ?>/assets/images/favicon.gif">


    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">


    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet">


    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>/assets/css/components.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>/assets/css/dashboard.css" rel="stylesheet">

    <script>
    window.APP = {
        baseUrl: "<?= BASE_URL ?>",
        appName: "<?= APP_NAME ?>"
    };
    </script>    

</head>


<body class="bg-light">


<?php

$currentPage = basename($_SERVER['PHP_SELF']);

if ($currentPage !== 'login.php') {

    include 'navbar.php';

}

?>


<div class="container page-container">