<?php
require_once("../public/dp.php");
require_once("adminheader.php");

/* ---------------- VALIDATE ID ---------------- */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: appointments.php");
    exit;
}
$appointment_id = (int)$_GET['id'];

/* ---------------- FETCH APPOINTMENT + USER ---------------- */
$appointment = $mysqli->query("
    SELECT a.*, u.name AS user_name
    FROM appointments a
    JOIN users u ON u.id = a.user_id
    WHERE a.id = $appointment_id
")->fetch_assoc();

if (!$appointment) {
    header("Location: appointments.php");
    exit;
}

/* ---------------- FETCH SERVICES + STATUS ---------------- */
$services = $mysqli->query("
    SELECT aps.id AS aps_id, s.name, aps.status
    FROM appointment_services aps
    JOIN services s ON s.id = aps.service_id
    WHERE aps.appointment_id = $appointment_id
");

/* ---------------- UPDATE SERVICE STATUS ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {

    foreach ($_POST['status'] as $aps_id => $new_status) {
        $aps_id = (int)$aps_id;
        $new_status = $mysqli->real_escape_string($new_status);

        // Update service-level status
        $mysqli->query("
            UPDATE appointment_services
            SET status='$new_status'
            WHERE id=$aps_id
        ");

        // Fetch service name for notification
        $sName = $mysqli->query("
            SELECT s.name
            FROM appointment_services aps
            JOIN services s ON s.id = aps.service_id
            WHERE aps.id=$aps_id
        ")->fetch_assoc()['name'];

        $dateFormatted = date("d M", strtotime($appointment['appointment_date']));
        $timeFormatted = date("g:i A", strtotime($appointment['appointment_time']));

        // Prepare message
        $message = match ($new_status) {
            'confirmed' => "Your appointment for $sName on $dateFormatted at $timeFormatted has been confirmed.",
            'completed' => "Your appointment for $sName on $dateFormatted at $timeFormatted has been completed. Thank you for visiting us.",
            'cancelled' => "Your appointment for $sName on $dateFormatted at $timeFormatted has been cancelled.",
            default => "Your appointment for $sName on $dateFormatted at $timeFormatted has been updated."
        };

        $mysqli->query("
            INSERT INTO notifications (user_id, appointment_id, message, is_read)
            VALUES ({$appointment['user_id']}, $appointment_id, '" . $mysqli->real_escape_string($message) . "', 0)
        ");
    }

    // Update overall appointment status
    $pendingCount = $mysqli->query("
        SELECT COUNT(*) AS c
        FROM appointment_services
        WHERE appointment_id=$appointment_id AND status!='completed'
    ")->fetch_assoc()['c'];

    $overallStatus = $pendingCount == 0 ? 'completed' : 'confirmed';
    $mysqli->query("UPDATE appointments SET status='$overallStatus' WHERE id=$appointment_id");

    header("Location: appointment.php?success=updated");
    exit;
}
?>

<div class="content">
    <div class="card shadow-sm">
        <div class="card-body">

            <h5 class="mb-4">Edit Appointment</h5>

            <div class="mb-2"><strong>User:</strong>
                <?= htmlspecialchars($appointment['user_name']) ?>
            </div>

            <div class="mb-2"><strong>Date:</strong>
                <?= date('d M Y', strtotime($appointment['appointment_date'])) ?>
            </div>

            <div class="mb-4"><strong>Time:</strong>
                <?= date('H:i', strtotime($appointment['appointment_time'])) ?>
            </div>

            <form method="post">
                <?php
                $services->data_seek(0); // reset pointer
                while ($s = $services->fetch_assoc()):
                ?>
                    <div class="mb-3">
                        <strong><?= htmlspecialchars($s['name']) ?></strong><br>
                        <select name="status[<?= $s['aps_id'] ?>]" class="form-select" required>
                            <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $s['status'] === $st ? 'selected' : '' ?>>
                                    <?= ucfirst($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endwhile; ?>

                <button type="submit" class="btn btn-success">Update Status</button>
                <a href="appointment.php" class="btn btn-secondary">Back</a>
            </form>

        </div>
    </div>
</div>