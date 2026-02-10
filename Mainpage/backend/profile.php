<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');


// login check
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}


$user_id = (int)$_SESSION['id'];
$user = $mysqli->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$success = "";
$error = "";
$password_error = "";

// ================= UPDATE PASSWORD & IMAGE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ---------- PASSWORD CHANGE ---------- */
    if (!empty($_POST['old_password'])) {

        if (!password_verify($_POST['old_password'], $user['password'])) {
            $password_error = "Old password is incorrect";
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $password_error = "Passwords do not match";
        } elseif (strlen($_POST['new_password']) < 6) {
            $password_error = "Password must be at least 6 characters";
        } else {
            $hashed = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $mysqli->query("UPDATE users SET password='$hashed' WHERE id=$user_id");
            $success = "Profile updated successfully";
        }
    }

    /* ---------- PROFILE IMAGE UPLOAD ---------- */
    if (!empty($_FILES['profile_image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['profile_image']['type'], $allowed)) {
            $error = "Only JPG or PNG allowed";
        } else {
            $imgName = time() . '_' . $_FILES['profile_image']['name'];
            move_uploaded_file($_FILES['profile_image']['tmp_name'], "../Mainpage/profile/$imgName");
            $mysqli->query("UPDATE users SET profile_image='$imgName' WHERE id=$user_id");
            $success = "Profile updated successfully";
        }
    }

    // reload user
    $user = $mysqli->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
}
?>
