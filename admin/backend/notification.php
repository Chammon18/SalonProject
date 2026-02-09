<?php
require_once(__DIR__ . '/../auth_check.php');

// Filters
$filterDate   = $_GET['date'] ?? '';
$filterStatus = 'pending'; // only show pending bookings by default
$search       = $_GET['search'] ?? ''; // define search variable

// Pagination
$limit  = 5;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = max(1, $page);
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = ["a.status = 'pending'"]; // default to pending only

if ($filterDate) {
    $safeDate = mysqli_real_escape_string($mysqli, $filterDate);
    $where[] = "DATE(a.appointment_date) = '$safeDate'";
}

if ($search) {
    $safeSearch = mysqli_real_escape_string($mysqli, $search);
    $where[] = "(u.name LIKE '%$safeSearch%' OR u.email LIKE '%$safeSearch%')";
}

$whereSql = "WHERE " . implode(" AND ", $where);

// Count total notifications
$countRes = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM notifications n
    JOIN appointments a ON n.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    $whereSql
");
$totalNotifications = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalNotifications / $limit);

// Fetch notifications
$notifQuery = $mysqli->query("
    SELECT
        n.id AS notif_id,
        n.message,
        n.created_at,
        n.is_read,
        a.id AS appointment_id,
        a.appointment_date,
        a.status,
        u.name,
        u.email
    FROM notifications n
    JOIN appointments a ON n.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    $whereSql
    ORDER BY n.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>

