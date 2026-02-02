<?php
// session_start();
require_once("../public/dp.php");
require_once("adminheader.php");

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

<div class="content">
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="user_management.php" class="btn btn-dark rounded">Back</a>
    </div>

    <div class="d-flex justify-content-center">
        <div class="col-md-6 col-12">
            <div class="card shadow rounded-4">
                <div class="card-body">
                    <h3 class="mb-4 text-center text-dark">Edit User Status</h3>

                    <?php if ($msg): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="1" <?= ($status == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($status == 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <input type="hidden" name="update" value="1">
                        <button class="btn btn-success w-100"><i class="fa fa-save me-2"></i>Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>