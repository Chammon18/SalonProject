<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $adminBase = preg_replace('#/admin(?:/frontend)?/.*$#', '/admin', $scriptName);
    if ($adminBase === $scriptName) {
        $adminBase = '/admin';
    }
    ?>
    <meta charset="UTF-8">
    <title>Admin Login | Angel's Palace</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($adminBase . '/style.css') ?>">
</head>

<body class="admin-login">

    <div class="login-card">
        <div class="login-header">
            <h4>Admin Login</h4>
            <p>Welcome back to <span class="brand">Angel’s Palace</span></p>
        </div>

        <div class="p-4 pt-2">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3 icon-input">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="Admin Email" required>
                </div>

                <div class="mb-3 icon-input">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <button class="btn btn-login w-100">
                    <i class="fa fa-arrow-right-to-bracket"></i> Login
                </button>
            </form>

            <!-- <div class="text-center mt-3 small text-muted">
                Default Admin: admin@angelspalace.com / admin123
            </div> -->
        </div>
    </div>

</body>

</html>