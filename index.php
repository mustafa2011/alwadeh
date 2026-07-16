<?php

/**
 * ============================================================================
 * ALWADEH ZATCA
 * Home Page
 * ============================================================================
 */

require_once __DIR__ . '/includes/page_bootstrap.php'; 

include 'includes/header.php';

$page = $_GET['page'] ?? 'dashboard';

$pageFile = __DIR__ . "/pages/{$page}.php";

if (file_exists($pageFile)) {
    include $pageFile;
}
?>

<!-- <script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script> -->

<?php

include 'includes/footer.php';