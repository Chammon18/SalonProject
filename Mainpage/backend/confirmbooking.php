<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');

// LOGIN CHECK
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

$user_id = (int)$_SESSION['id'];

// Get appointment_id from GET (or latest if none)
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$appointment_group_id = '';

if ($appointment_id) {
    $groupRes = $mysqli->query("
        SELECT appointment_group_id
        FROM appointments
        WHERE id = $appointment_id AND user_id = $user_id
        LIMIT 1
    ");
    if ($groupRes->num_rows > 0) {
        $appointment_group_id = $groupRes->fetch_assoc()['appointment_group_id'];
    }
}

if (!$appointment_group_id) {
    // fallback to latest booking group
    $res = $mysqli->query("
        SELECT appointment_group_id
        FROM appointments 
        WHERE user_id = $user_id 
        ORDER BY id DESC 
        LIMIT 1
    ");
    if ($res->num_rows === 0) {
        echo "<div class='container my-5'><div class='alert alert-warning'>No booking found.</div></div>";
        exit;
    }
    $appointment_group_id = $res->fetch_assoc()['appointment_group_id'];
}

// Fetch one appointment row for date/time/status
$appointment_res = $mysqli->query("
    SELECT *
    FROM appointments
    WHERE appointment_group_id = '$appointment_group_id' AND user_id = $user_id
    ORDER BY id ASC
    LIMIT 1
");
if ($appointment_res->num_rows === 0) {
    echo "<div class='container my-5'><div class='alert alert-warning'>Booking not found.</div></div>";
    exit;
}
$appointment = $appointment_res->fetch_assoc();

// Fetch user info
$user_res = $mysqli->query("SELECT name, phone FROM users WHERE id = $user_id");
$user = $user_res->fetch_assoc();

// Fetch all services for this appointment group
$services_res = $mysqli->query("
    SELECT s.name AS service_name, c.name AS category_name, s.description, s.price, s.duration
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE a.appointment_group_id = '$appointment_group_id'
      AND a.user_id = $user_id
    ORDER BY c.name, s.name
");

// Group services by category
$services_by_category = [];
$service_details = [];
while ($row = $services_res->fetch_assoc()) {
    $services_by_category[$row['category_name']][] = $row['service_name'];
    $service_details[] = $row;
}
?>
