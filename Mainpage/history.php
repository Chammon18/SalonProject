<?php
session_start();
require_once("../public/dp.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
$user_id = (int)$_SESSION['id'];

$sql = "
SELECT
    a.id,
    s.name AS service_name,
    s.price,
    ap.appointment_date,
    ap.appointment_time,
    a.status
FROM appointment_services a
JOIN appointments ap ON a.appointment_id = ap.id
JOIN services s ON a.service_id = s.id
WHERE ap.user_id = $user_id
  AND a.status = 'completed'
ORDER BY ap.appointment_date DESC
";



$result = $mysqli->query($sql);
require_once("../include/header.php");
?>

<div class="container mt-5">
    <h3 class="text-center mb-5 fw-bold">My Service History</h3>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="history-card">
                        <div class="card-header">
                            <?= htmlspecialchars($row['service_name']) ?>
                            <span class="status-badge"><?= $row['status'] ?></span>
                        </div>
                        <div class="card-body">
                            <p><i class="fa fa-calendar me-2"></i><?= date('d M Y', strtotime($row['appointment_date'])) ?></p>
                            <p><i class="fa fa-clock me-2"></i><?= date('h:i A', strtotime($row['appointment_time'])) ?></p>
                            <p><i class="fa fa-dollar-sign me-2"></i>$<?= number_format($row['price'], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-history">
            <i class="fa fa-bell-slash fa-3x"></i>
            <p>No completed services yet.</p>
        </div>
    <?php endif; ?>
</div>