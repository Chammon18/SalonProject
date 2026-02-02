<?php
// session_start();
require_once("../public/dp.php");
require_once("adminheader.php");

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

<div class="content">
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="card-create-admin">
            <h3><i class="fa fa-user-shield me-2"></i>Create Admin</h3>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Admin Name" value="<?= htmlspecialchars($name) ?>" required>
                    <label for="floatingName"><i class="fa fa-user me-2"></i>Name</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="admin@example.com" value="<?= htmlspecialchars($email) ?>" required>
                    <label for="floatingEmail"><i class="fa fa-envelope me-2"></i>Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" value="<?= htmlspecialchars($password) ?>" required>
                    <label for="floatingPassword"><i class="fa fa-lock me-2"></i>Password</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Phone" value="<?= htmlspecialchars($phone) ?>" required>
                    <label for="floatingPhone"><i class="fa fa-phone me-2"></i>Phone</label>
                </div>

                <button class="btn btn-create"><i class="fa fa-plus me-2"></i>Create Admin</button>
            </form>

            <a href="index.php" class="back-link"><i class="fa fa-arrow-left me-1"></i>Back to Login again</a>
        </div>
    </div>
</div>