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
    a.service_id,
    s.name AS service_name,
    s.price,
    s.description,
    a.appointment_date,
    a.appointment_time,
    a.status
FROM appointments a
JOIN services s ON a.service_id = s.id
WHERE a.user_id = $user_id
  AND a.status = 'completed'
ORDER BY a.appointment_date DESC
";

$result = $mysqli->query($sql);
require_once("../include/header.php");
?>

<div class="container my-5">
    <h3 class="mb-4 fw-bold text-center">Completed Service History</h3>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['service_name']) ?></h5>

                            <?php if (!empty($row['description'])): ?>
                                <p class="card-text text-muted"><?= htmlspecialchars($row['description']) ?></p>
                            <?php endif; ?>

                            <p class="card-text mb-1">
                                <strong>Date:</strong> <?= date('d M Y', strtotime($row['appointment_date'])) ?>
                            </p>
                            <p class="card-text mb-1">
                                <strong>Time:</strong> <?= date('h:i A', strtotime($row['appointment_time'])) ?>
                            </p>
                            <p class="card-text mb-3">
                                <strong>Price:</strong> <span class="text-success">Kyats <?= number_format($row['price']) ?></span>
                            </p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="badge bg-success px-3 py-2">Completed</span>
                                <a href="rebook.php?service_id=<?= $row['service_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    Re-Book
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-muted mt-5">No completed services found.</p>
    <?php endif; ?>
</div>
