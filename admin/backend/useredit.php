<?php
require_once(__DIR__ . '/../auth_check.php');
// session_start();

// Only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get user ID from URL
$user_id = $_GET['id'] ?? 0;
$user_id = (int)$user_id;

if (!$user_id) {
    die("Invalid user ID");
}

// Fetch user info
$user = $mysqli->query("SELECT * FROM users WHERE id=$user_id LIMIT 1")->fetch_assoc();
if (!$user) {
    die("User not found");
}

// Initialize status
$status = $user['status'];

// Handle form submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $status = $_POST['status'] == '1' ? 1 : 0;
    $mysqli->query("UPDATE users SET status=$status WHERE id=$user_id");
    $msg = "User status updated successfully!";
}

?>

