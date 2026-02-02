<?php
// session_start();
require_once("../public/dp.php");
require_once("../public/common_query.php");

$user_id = $_SESSION['id'] ?? 0;
$isLoggedIn = isset($_SESSION['id']) && !empty($_SESSION['id']);


// Only fetch user info if logged in
if ($isLoggedIn) {
    // Fetch user info
    $userNav = $mysqli->query("SELECT name, profile_image FROM users WHERE id=$user_id")->fetch_assoc();
    $avatar = !empty($userNav['profile_image'])
        ? "../Mainpage/profile/" . $userNav['profile_image']
        : "../image/default.jpg";

    // Fetch unread notifications count
    $count_res = $mysqli->query("
    SELECT COUNT(*) AS total 
    FROM notifications 
    WHERE user_id = $user_id 
      AND show_in_nav = 1
      AND is_read = 0
");

    $notif_count = $count_res->fetch_assoc()['total'];

    // Fetch latest 5 notifications

    $nav_notif_res = $mysqli->query("
    SELECT * 
    FROM notifications
    WHERE user_id = $user_id
      AND show_in_nav = 1
    ORDER BY created_at DESC
    LIMIT 5
");



    // Fetch completed service count
    $completed_res = $mysqli->query("
    SELECT COUNT(*) AS completed_count
    FROM appointment_services a
    JOIN appointments ap ON a.appointment_id = ap.id
    WHERE ap.user_id = $user_id
      AND a.status = 'completed'
");

    $completed_count = $completed_res->fetch_assoc()['completed_count'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angel's Palace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    <link rel="stylesheet" href="/SalonProject/style.css">
    <!-- alert sweeet -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="../Mainpage/index.php">
                <img src="../image/pp.jpg" alt="Angel's Palace Logo" class="nav-logo">
                <span>Angel's Palace</span>
            </a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link active" href="../Mainpage/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Mainpage/services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Mainpage/about.php">Contact Us</a></li>
                    <li class="nav-item ms-lg-3"><a href="../Mainpage/book.php"
                            class="btn btn-login"
                            id="bookNowBtn">
                            Book Now
                        </a>
                    </li>

                    <!-- Notifications -->
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-bell fa-lg"></i>
                                <?php if ($notif_count > 0): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="notif-badge">
                                        <?= $notif_count ?>
                                    </span>
                                <?php endif; ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="width: 300px;">
                                <li class="dropdown-header fw-bold p-2 bg-light">Notifications</li>
                                <li>
                                    <hr class="dropdown-divider m-0">
                                </li>

                                <?php if ($nav_notif_res->num_rows > 0): ?>
                                    <?php while ($n = $nav_notif_res->fetch_assoc()): ?>
                                        <li class="px-3 py-2 <?= $n['is_read'] == 0 ? 'bg-light fw-bold' : '' ?>">
                                            <?= htmlspecialchars($n['message']) ?><br>
                                            <small class="text-muted"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <li class="text-center text-muted py-2">No notifications</li>
                                <?php endif; ?>

                                <li>
                                    <hr class="dropdown-divider m-0">
                                </li>
                                <li class="text-center">
                                    <a href="clear_noti.php" class="dropdown-item text-danger fw-bold">Clear All</a>
                                    <a href="notification.php" class="dropdown-item fw-bold">View All</a>

                                </li>
                            </ul>
                        </li>

                        <!-- User Dropdown -->

                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                                href="#" data-bs-toggle="dropdown">
                                <img src="<?= $avatar ?? '../image/default.jpg' ?>"
                                    class="rounded-circle"
                                    width="40" height="40"
                                    style="object-fit:cover;">
                                <?= htmlspecialchars($userNav['name'] ?? 'User') ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="../Mainpage/profile.php">Profile</a></li>
                                <li>
                                    <a class="dropdown-item" href="history.php">
                                        Service History
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../Mainpage/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>


                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS (required for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Mark notifications as read when dropdown opens -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notifDropdown = document.getElementById('notificationDropdown');
            if (notifDropdown) {
                notifDropdown.addEventListener('show.bs.dropdown', async () => {

                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.style.display = 'none';
                });
            }
        });

        // user is not login show alert
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        document.addEventListener('DOMContentLoaded', () => {
            const bookBtn = document.getElementById('bookNowBtn');

            if (!bookBtn) return;

            bookBtn.addEventListener('click', function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Required',
                        text: 'Please log in first to book our services.',
                        showCancelButton: true,
                        confirmButtonText: 'Login Now',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        background: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../Mainpage/login.php';
                        }
                    });
                }
            });
        });
    </script>