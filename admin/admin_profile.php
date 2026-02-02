<?php

require_once("auth_check.php"); // Checks if admin is logged in
require_once("../public/dp.php");


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
require_once("adminheader.php");
?>

<div class="content text-white">
    <div class="d-flex text-white justify-content-between mb-3">
        <!-- <h3 class="text-white">Admin Profile</h3> -->
        <a href="admin_create.php" class="btn btn-dark"><i class="fa fa-plus"></i> Create Admin</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="card shadow border-0 rounded-4 p-4">

        <!-- PROFILE IMAGE -->
        <div class="text-center mb-4">
            <img
                src="../admin/profile/<?= !empty($user['profile_image'])
                                            ? htmlspecialchars($user['profile_image'])
                                            : 'default.png' ?>"
                class="rounded-circle mb-2"
                width="120"
                height="120"
                style="object-fit:cover">


            <form method="post" enctype="multipart/form-data" class="mt-2">
                <input type="hidden" name="action" value="profile_image">
                <input type="file" name="profile_image" class="form-control mb-2" required>
                <button class="btn btn-outline-success btn-sm ">Change Photo</button>
            </form>
        </div>

        <!-- USER INFO (READ ONLY) -->
        <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></div>
        <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>

        <hr>

        <!-- PASSWORD CHANGE -->

        <form method="post" class="mt-3">
            <input type="hidden" name="action" value="password_change">

            <small class="text-muted d-block mb-2">
                Leave password fields empty if you don’t want to change your password.
            </small>

            <input type="password" name="old_password" class="form-control mb-2" placeholder="Old Password">
            <input type="password" name="new_password" class="form-control mb-2" placeholder="New Password">
            <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password">

            <button type="submit" class="btn btn-success btn-sm">
                <i class="fa fa-save"></i> Update
            </button>
        </form>
    </div>
</div>