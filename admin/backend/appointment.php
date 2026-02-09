<?php
require_once(__DIR__ . '/../auth_check.php');
//filter
$selectedDate  = $_GET['date'] ?? date('Y-m-d');
$statusFilter  = $_GET['status'] ?? '';
$statusSafe    = $mysqli->real_escape_string($statusFilter);



$sql = "
SELECT
    a.id AS appointment_id,
    a.appointment_date,
    a.appointment_time,
    a.request,
    a.status AS appointment_status,
    u.name AS user_name,
    u.phone AS user_phone,
    s.id AS service_id,
    s.name AS service_name,
    s.duration AS duration_raw,
    s.category_id AS category_id,
    st.name AS staff_name
FROM appointments a
JOIN users u ON u.id = a.user_id
JOIN services s ON s.id = a.service_id
LEFT JOIN staff st ON st.id = a.staff_id
WHERE a.appointment_date = '$selectedDate'
" . ($statusSafe ? " AND a.status = '$statusSafe'" : "") . "
 ORDER BY 
    FIELD(a.status, 'pending', 'approved', 'completed', 'cancelled'),
    a.appointment_time ASC

    ";


$result = $mysqli->query($sql);
$isPastSelected = strtotime($selectedDate) < strtotime(date('Y-m-d'));

?>

