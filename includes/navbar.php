<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">

    <div class="container">

        <a href="<?= BASE_URL ?>" class="navbar-brand fw-bold">

            <i class="bi bi-receipt"></i>

            ALWADEH ZATCA Portal

        </a>

        <div class="d-flex align-items-center ms-auto">

            <?php if ($currentUser): ?>

                <span class="me-3 text-white">

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars($currentUser['full_name']) ?>

                </span>

                <a href="<?= BASE_URL ?>/app/auth/logout.php" class="btn btn-outline-light btn-sm">

                    <i class="bi bi-box-arrow-right"></i>

                    Logout

                </a>


            <?php endif; ?>

        </div>

    </div>

</nav>