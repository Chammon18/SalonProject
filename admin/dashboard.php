<?php
// session_start();
require_once("../public/dp.php");
require_once("adminheader.php");

// ---------- Dashboard Stats ----------

// TOTAL USERS
$userCount = $mysqli->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// TOTAL SERVICES
$serviceCount = $mysqli->query("SELECT COUNT(*) AS total FROM services")->fetch_assoc()['total'];

// TODAY APPOINTMENTS
$todayCount = $mysqli->query("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['total'];

// PENDING APPOINTMENTS
$pendingCount = $mysqli->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'pending'")->fetch_assoc()['total'];

// Popular services
$popularquery = "
SELECT s.name, s.image, s.description, COUNT(aps.id) AS total_booked
FROM appointment_services aps
JOIN services s ON aps.service_id = s.id
GROUP BY aps.service_id
ORDER BY total_booked DESC
LIMIT 5
";
$result = mysqli_query($mysqli, $popularquery);

// UNREAD BOOKING NOTIFICATIONS
$notificationCount = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM notifications n
    JOIN appointments a ON n.appointment_id = a.id
    WHERE n.is_read = 0 AND a.status = 'pending'
")->fetch_assoc()['total'];

?>

<div class="content">
    <div class="row mb-3">

        <!-- NOTIFICATIONS -->
        <div class="col-md-3">
            <a href="notification.php" class="text-decoration-none text-dark">
                <div class="card shadow border-0 rounded-4 stat-card hover-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon bg-info position-relative">
                            <i class="fa-solid fa-bell"></i>

                            <?php if ($notificationCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $notificationCount ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">New Bookings</h6>
                            <h3 class="fw-bold"><?= $notificationCount ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>



        <!-- RIGHT: Quick Stats Cards -->
        <div class="col-md-9">
            <div class="row g-3">
                <!-- CUSTOMERS -->
                <div class="col-md-3">
                    <a href="user_management.php?tab=user" class="text-decoration-none text-dark">
                        <div class="card shadow border-0 rounded-4 stat-card hover-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon bg-success">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Customers</h6>
                                    <h3 class="fw-bold"><?= $userCount ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- SERVICES -->
                <div class="col-md-3">
                    <a href="services.php" class="text-decoration-none text-dark">
                        <div class="card shadow border-0 rounded-4 stat-card hover-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon bg-primary">
                                    <i class="fa-solid fa-spa"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Services</h6>
                                    <h3 class="fw-bold"><?= $serviceCount ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- TODAY -->
                <div class="col-md-3">
                    <a href="appointment.php?filter=today" class="text-decoration-none text-dark">
                        <div class="card shadow border-0 rounded-4 stat-card hover-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon bg-warning">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Today</h6>
                                    <h3 class="fw-bold"><?= $todayCount ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- PENDING -->
                <div class="col-md-3">
                    <a href="appointment.php?status=pending" class="text-decoration-none text-dark">
                        <div class="card shadow border-0 rounded-4 stat-card hover-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon bg-danger">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 text-muted">Pending</h6>
                                    <h3 class="fw-bold"><?= $pendingCount ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Today's Appointments Table -->
    <div class="card mt-3">
        <h3>Today’s Appointments</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $todayList = $mysqli->query("
                    SELECT a.id, u.name, a.appointment_time, a.status,
                    GROUP_CONCAT(s.name SEPARATOR ', ') AS services
                    FROM appointments a
                    JOIN users u ON a.user_id = u.id
                    JOIN appointment_services aps ON a.id = aps.appointment_id
                    JOIN services s ON aps.service_id = s.id
                    WHERE DATE(a.appointment_date) = CURDATE()
                    GROUP BY a.id
                    ORDER BY a.appointment_time ASC
                ");

                while ($row = $todayList->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['services']) ?></td>
                        <td><?= date("h:i A", strtotime($row['appointment_time'])) ?></td>
                        <td>
                            <span class="status <?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Row 3: Popular Services -->
    <div class="card shadow-sm p-3 mt-3">
        <h5 class="mb-3">🔥 Most Booked Services</h5>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <ul class="list-group list-group-flush">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['image']) ?>" width="50" height="50" class="rounded">
                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($row['description']) ?></small>
                        </div>
                        <span class="badge bg-success">
                            <?= $row['total_booked'] ?> bookings
                        </span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No booking data yet.</p>
        <?php endif; ?>
    </div>


</div>