<?php
require_once "../public/dp.php";          // your database connection
require '../vendor/autoload.php';

if (!isset($_GET['token'])) {
    die("Invalid request!");
}

$token = $_GET['token'];

// Check token validity
$result = $mysqli->query("SELECT id, token_expiry FROM users WHERE reset_token='$token'");
if ($result->num_rows === 0) die("Invalid or expired token!");

$user = $result->fetch_assoc();
if (strtotime($user['token_expiry']) < time()) die("Token expired!");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $mysqli->query("UPDATE users SET password='$password', reset_token=NULL, token_expiry=NULL WHERE id=" . $user['id']);
    echo "Password reset successfully! <a href='login.php'>Login</a>";
}
?>

<form method="POST">
    <input type="password" name="password" placeholder="Enter new password" required>
    <button type="submit">Reset Password</button>
</form>