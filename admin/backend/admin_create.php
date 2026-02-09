<?php
require_once(__DIR__ . '/../auth_check.php');
// session_start();

// Only admin can create new admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch current admin role
$currentAdmin = $mysqli->query("SELECT role FROM users WHERE id=" . $_SESSION['admin_id'])->fetch_assoc();
if ($currentAdmin['role'] !== 'admin') {
    die("Access Denied: Only admin can create admin accounts.");
}

$success = $error = "";

// Initialize form values
$name = $email = $password = $phone = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $role = 'admin'; // only create admin

    if (!$name || !$email || !$password) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $mysqli->query("INSERT INTO `users` (`name`,`email`,`password`,`phone`,`role`) VALUES ('$name','$email','$hashed','$phone','$role')");
        $success = "Admin account created successfully!";
        header("Location:index.php");

        // Clear form values after successful creation
        $name = $email = $password = $phone = "";
    }
}
?>

