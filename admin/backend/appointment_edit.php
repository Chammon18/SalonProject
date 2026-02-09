<?php
require_once(__DIR__ . '/../auth_check.php');
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
    SELECT a.id AS appointment_id, s.name, s.price, s.category_id, a.status, a.staff_id
    FROM appointments a
    JOIN services s ON s.id = a.service_id
    WHERE a.appointment_group_id = '{$appointment['appointment_group_id']}'
    ORDER BY s.name ASC
");

/* ---------------- GROUP TOTAL + SERVICES LIST ---------------- */
$serviceItems = [];
$groupTotal = 0.0;
if ($services && $services->num_rows > 0) {
    $services->data_seek(0);
    while ($srv = $services->fetch_assoc()) {
        $serviceItems[] = $srv;
        $groupTotal += (float)$srv['price'];
    }
}
$services->data_seek(0);

$paymentExists = false;
$paymentCheck = $mysqli->query("
    SELECT p.id
    FROM payments p
    JOIN appointments a ON a.id = p.appointment_id
    WHERE a.appointment_group_id = '{$appointment['appointment_group_id']}'
    LIMIT 1
");
if ($paymentCheck && $paymentCheck->num_rows > 0) {
    $paymentExists = true;
}

/* ---------------- UPDATE SERVICE STATUS ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    if ($isPast) {
        header("Location: appointment.php?error=past_locked");
        exit;
    }

    $paymentMethod = $_POST['payment_method'] ?? '';
    $hasCompleted = in_array('completed', $_POST['status'], true);

    if ($hasCompleted && !$paymentExists && $paymentMethod === '') {
        header("Location: appointment_edit.php?id=$appointment_id&error=payment_required");
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

    if ($hasCompleted && !$paymentExists && $paymentMethod !== '') {
        $paymentMethodSafe = $mysqli->real_escape_string($paymentMethod);
        $amountSafe = number_format((float)$groupTotal, 2, '.', '');
        $paidAt = date('Y-m-d H:i:s');
        $firstApptId = $appointment_id;
        $firstRow = $mysqli->query("
            SELECT id
            FROM appointments
            WHERE appointment_group_id = '{$appointment['appointment_group_id']}'
            ORDER BY id ASC
            LIMIT 1
        ")->fetch_assoc();
        if ($firstRow && isset($firstRow['id'])) {
            $firstApptId = (int)$firstRow['id'];
        }

        $mysqli->query("
            INSERT INTO payments (appointment_id, amount, payment_method, paid_at)
            VALUES ($firstApptId, $amountSafe, '$paymentMethodSafe', '$paidAt')
        ");
    }

    header("Location: appointment.php?success=updated");
    exit;
}
?>

