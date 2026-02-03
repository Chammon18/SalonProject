<?php
session_start();
require_once("../public/dp.php");

/* AUTO CREATE DEFAULT ADMIN (ONLY ONCE) */
$check = mysqli_query($mysqli, "SELECT id FROM users WHERE role='admin' LIMIT 1");
if (mysqli_num_rows($check) === 0) {
    $email = "admin@angelspalace.com";
    $password = password_hash("admin123", PASSWORD_DEFAULT);

    mysqli_query(
        $mysqli,
        "INSERT INTO users (name, email, password, role, status)
         VALUES ('Super Admin', '$email', '$password', 'admin', 'active')"
    );
}

$error = '';

/* LOGIN LOGIC */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query(
        $mysqli,
        "SELECT id, name, password, status
         FROM users 
         WHERE email='$email' AND role='admin'
         LIMIT 1"
    );

    if ($result && mysqli_num_rows($result) === 1) {

        $admin = mysqli_fetch_assoc($result);

        // BLOCK INACTIVE ADMIN
        if ($admin['status'] !== 'active') {
            $error = "Your admin account is inactive. Please contact super admin.";
        }
        // PASSWORD CHECK
        elseif (password_verify($password, $admin['password'])) {

            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['role']       = 'admin';

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid admin email or password";
        }
    } else {
        $error = "Invalid admin email or password";
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login | Angel's Palace</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #6BCF9B, #F4A6C1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            width: 380px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .login-header {
            text-align: center;
            padding: 30px 20px 10px;
        }

        .login-header h4 {
            font-weight: 700;
            color: #2f7d57;
        }

        .login-header p {
            font-size: 14px;
            color: #777;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
        }

        .btn-login {
            background: linear-gradient(135deg, #2f7d57, #f06292);
            border: none;
            color: #fff;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-login:hover {
            opacity: 0.9;
        }

        .icon-input {
            position: relative;
        }

        .icon-input i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #aaa;
        }

        .icon-input input {
            padding-left: 40px;
        }

        .brand {
            font-weight: 700;
            color: #f06292;
        }
    </style>
</head>

<body>

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