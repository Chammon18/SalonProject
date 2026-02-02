<?php
require_once "../public/dp.php";

$notif_id = isset($_GET['notif_id']) ? (int)$_GET['notif_id'] : 0;

if ($notif_id > 0) {
    // Mark as read
    $mysqli->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id");

    // Get appointment_id
    $res = $mysqli->query("SELECT appointment_id FROM notifications WHERE id = $notif_id");
    if ($res && $row = $res->fetch_assoc()) {
        header("Location: appointment.php?id=" . $row['appointment_id']);
        exit;
    }
}

header("Location: notification.php");
exit;
