<?php
require_once(__DIR__ . '/../../public/dp.php');
session_start();

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

