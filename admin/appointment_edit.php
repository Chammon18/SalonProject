<?php
require_once("../public/dp.php");
require_once("adminheader.php");

/* ---------------- VALIDATE ID ---------------- */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: appointment.php");
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
    header("Location: appointment.php");
    exit;
}

$isPast = strtotime($appointment['appointment_date']) < strtotime(date('Y-m-d'));

/* ---------------- FETCH SERVICES + STATUS ---------------- */
$services = $mysqli->query("
    SELECT a.id AS appointment_id, s.name, s.category_id, a.status, a.staff_id
    FROM appointments a
    JOIN services s ON s.id = a.service_id
    WHERE a.appointment_group_id = '{$appointment['appointment_group_id']}'
    ORDER BY s.name ASC
");

/* ---------------- UPDATE SERVICE STATUS ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    if ($isPast) {
        header("Location: appointment.php?error=past_locked");
        exit;
    }

    foreach ($_POST['status'] as $appt_id => $new_status) {
        $appt_id = (int)$appt_id;
        $new_status = $mysqli->real_escape_string($new_status);
        $staff_id = isset($_POST['staff'][$appt_id]) ? (int)$_POST['staff'][$appt_id] : 0;
        $staff_sql = $staff_id > 0 ? $staff_id : "NULL";

        // Update appointment row (service-level)
        $mysqli->query("
            UPDATE appointments
            SET status='$new_status', staff_id=$staff_sql
            WHERE id=$appt_id
        ");

        // Fetch service name for notification
        $sName = $mysqli->query("
            SELECT s.name
            FROM appointments a
            JOIN services s ON s.id = a.service_id
            WHERE a.id=$appt_id
        ")->fetch_assoc()['name'];

        $dateFormatted = date("d M", strtotime($appointment['appointment_date']));
        $timeFormatted = date("g:i A", strtotime($appointment['appointment_time']));

        // Prepare message
        $message = match ($new_status) {
            'approved' => "Your appointment for $sName on $dateFormatted at $timeFormatted has been approved.",
            'completed' => "Your appointment for $sName on $dateFormatted at $timeFormatted has been completed. Thank you for visiting us.",
            'cancelled' => "Your appointment for $sName on $dateFormatted at $timeFormatted has been cancelled.",
            default => "Your appointment for $sName on $dateFormatted at $timeFormatted has been updated."
        };

        $mysqli->query("
            INSERT INTO notifications (user_id, appointment_id, message, is_read)
            VALUES ({$appointment['user_id']}, $appt_id, '" . $mysqli->real_escape_string($message) . "', 0)
        ");
    }

    // Update overall appointment status for the group
    $pendingCount = $mysqli->query("
        SELECT COUNT(*) AS c
        FROM appointments
        WHERE appointment_group_id = '{$appointment['appointment_group_id']}'
          AND status NOT IN ('completed','cancelled')
    ")->fetch_assoc()['c'];

    $overallStatus = $pendingCount == 0 ? 'completed' : 'approved';
    $mysqli->query("
        UPDATE appointments
        SET status='$overallStatus'
        WHERE appointment_group_id = '{$appointment['appointment_group_id']}'
          AND status NOT IN ('completed','cancelled')
    ");

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

            <?php if ($isPast): ?>
                <div class="alert alert-secondary">
                    This appointment is in the past. Editing is disabled to protect records.
                </div>
            <?php endif; ?>

            <form method="post">
                <?php
                $services->data_seek(0); // reset pointer
                while ($s = $services->fetch_assoc()):
                    $categoryId = (int)$s['category_id'];
                    $staffList = $mysqli->query("
                        SELECT st.id, st.name
                        FROM staff st
                        JOIN staff_categories sc ON sc.staff_id = st.id
                        WHERE sc.category_id = $categoryId
                        ORDER BY st.name ASC
                    ");
                ?>
                    <div class="mb-3">
                        <strong><?= htmlspecialchars($s['name']) ?></strong><br>
                        <select name="status[<?= $s['appointment_id'] ?>]" class="form-select" required <?= $isPast ? 'disabled' : '' ?>>
                            <?php foreach (['pending', 'approved', 'completed', 'cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $s['status'] === $st ? 'selected' : '' ?>>
                                    <?= ucfirst($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="staff[<?= $s['appointment_id'] ?>]" class="form-select mt-2" <?= $isPast ? 'disabled' : '' ?>>
                            <option value="0">Unassigned</option>
                            <?php while ($stf = $staffList->fetch_assoc()): ?>
                                <option value="<?= $stf['id'] ?>" <?= ((int)$s['staff_id'] === (int)$stf['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stf['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endwhile; ?>

                <button type="submit" class="btn btn-success" <?= $isPast ? 'disabled' : '' ?>>Update Status</button>
                <a href="appointment.php" class="btn btn-secondary">Back</a>
            </form>

        </div>
    </div>
</div>
