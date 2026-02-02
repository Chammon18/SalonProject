<?php
// session_start();
require_once("../public/dp.php");
require_once("auth_check.php"); // ensure admin is logged in


// Redirect if admin not logged in or session missing
if (!isset($_SESSION['admin_name'])) {
    header("Location: index.php?error=Please login first");
    exit;
}
$adminName = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Welcom Admin</title>
</head>

<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4 class="text-center text-white mt-4">
            Welcome Admin <br>
            <span class="text-white"><?php echo htmlspecialchars($adminName); ?></span>
        </h4>
        <a href="dashboard.php"><i class="fa-solid fa-grip"></i> Dashboard</a>
        <a href="services.php"><i class="fa-solid fa-list"></i> Services</a>
        <a href="appointment.php"><i class="fa-regular fa-calendar-check"></i> Appointments</a>
        <a href="user_management.php"><i class="fa-solid fa-users"></i> User Management</a>
        <a href="notification.php"><i class="fa-regular fa-bell"></i> Notifications</a>
        <a href="admin_profile.php"><i class="fa-solid fa-user-tie"></i> Profile</a>
        <a href="admin_logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> LogOut</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <!-- Your page content goes here -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-HoA+vP0Fdxi2hIWkH5qzN4jD1bHeQO5f5E33kI3r6ip0p/3kV6N6u1o7vH+RwXel" crossorigin="anonymous"></script>
</body>

</html>