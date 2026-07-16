<?php

declare(strict_types=1);

session_start();

include '../includes/header.php';

// إذا كان المستخدم مسجل دخول بالفعل
if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$hideNavbar = true;


?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ALWADEH ZATCA Portal - Login</title>

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-lg-4 col-md-6">

            <div class="card shadow">

                <div class="card-header text-center">

                    <h4>ALWADEH ZATCA Portal</h4>

                </div>

                <div class="card-body">

                    <form id="loginForm">

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    required
                                    autocomplete="username">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    required
                                    autocomplete="current-password">

                        </div>

                        <div id="loginMessage"
                             class="alert alert-danger d-none">
                        </div>

                        <button
                                type="submit"
                                class="btn btn-primary w-100">

                            Login

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

document
.getElementById('loginForm')
.addEventListener('submit', async function (e) {

    e.preventDefault();

    const formData = new FormData(this);

    const response = await fetch('<?= BASE_URL ?>/api/auth/login.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (result.success) {

        window.location.replace('<?= BASE_URL ?>/');

        return;
    }
    const message = document.getElementById('loginMessage');

    message.classList.remove('d-none');

    message.innerHTML = result.message;

});

</script>

</body>

</html>

<?php

include '../includes/footer.php';
