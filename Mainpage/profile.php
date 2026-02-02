<?php
session_start();
require_once("../public/dp.php");


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
require_once("../include/header.php");
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">My Profile</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <!-- PROFILE IMAGE -->
                    <div class="text-center mb-4">
                        <img
                            src="./profile/<?= !empty($user['profile_image']) ? $user['profile_image'] : 'default.png' ?>"
                            class="rounded-circle mb-2"
                            width="120"
                            height="120"
                            style="object-fit:cover">


                        <form method="post" enctype="multipart/form-data">
                            <input type="file" name="profile_image" class="form-control mt-2">
                            <button class="btn btn-outline-success btn-sm mt-2">Change Photo</button>
                        </form>
                    </div>

                    <!-- USER INFO (READ ONLY) -->
                    <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></div>
                    <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                    <div class="mb-3"><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></div>

                    <hr>

                    <!-- PASSWORD CHANGE -->
                    <form method="post">
                        <h6 class="mb-3">Change Password</h6>

                        <input type="password" name="old_password" class="form-control mb-2" placeholder="Old Password">
                        <input type="password" name="new_password" class="form-control mb-2" placeholder="New Password">
                        <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password">

                        <?php if ($password_error): ?>
                            <div class="text-danger mb-2"><?= $password_error ?></div>
                        <?php endif; ?>

                        <button class="btn btn-success w-100">Update Profile</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once("../include/footer.php"); ?>