<?php
require_once(__DIR__ . '/../auth_check.php');
require_once("auth_check.php"); // Checks if admin is logged in

// Get logged-in admin ID
$admin_id = $_SESSION['admin_id'] ?? 0;

// Initialize messages
$msg = '';
$error_msg = '';

// Fetch admin data
$user = [];
if ($admin_id) {
    $result = mysqli_query($mysqli, "SELECT * FROM users WHERE id=$admin_id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // PROFILE IMAGE UPLOAD
    if ($action === 'profile_image' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        // allow only image types (important!)
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $error_msg = "Only JPG, PNG, WEBP images are allowed!";
        } else {

            $filename = "profile_$admin_id.$ext";
            $upload_dir = __DIR__ . "/profile/"; // admin/profile/

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $filename)) {
                mysqli_query($mysqli, "UPDATE users SET profile_image='$filename' WHERE id=$admin_id");
                header("Location: admin_profile.php");
                exit;
            } else {
                $error_msg = "Failed to upload image!";
            }
        }
    }


    // PASSWORD CHANGE
    // PASSWORD CHANGE (OPTIONAL)
    if ($action === 'password_change') {
        $old = trim($_POST['old_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        // If all password fields are empty → do nothing (allowed)
        if ($old === '' && $new === '' && $confirm === '') {
            $msg = "Profile updated successfully!";
        }
        // If any field is filled → validate
        else {
            if (!$old || !$new || !$confirm) {
                $error_msg = "Please fill all password fields to change password!";
            } elseif (!password_verify($old, $user['password'])) {
                $error_msg = "Old password is incorrect!";
            } elseif ($new !== $confirm) {
                $error_msg = "New passwords do not match!";
            } else {
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                mysqli_query($mysqli, "UPDATE users SET password='$new_hash' WHERE id=$admin_id");
                $msg = "Password updated successfully!";
            }
        }
    }
}

?>

