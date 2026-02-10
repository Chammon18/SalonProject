<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');

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
?>
