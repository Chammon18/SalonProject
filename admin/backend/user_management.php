<?php
require_once(__DIR__ . '/../auth_check.php');
require_once "auth_check.php";

// Tab
$tab = $_GET['tab'] ?? 'user';

// Filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// 
// PAGINATION (ADDED)
// 
$limit = 5; // users per page
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max(1, $page);
$offset = ($page - 1) * $limit;
// 

// Build WHERE clause
$where = [];
$where[] = $tab === 'user' ? "role='user'" : "role='admin'";

// Search by name or email
if ($search) {
    $safeSearch = mysqli_real_escape_string($mysqli, $search);
    $where[] = "(name LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%')";
}

// Filter by status
if ($statusFilter === 'active') {
    $where[] = "status = 1";
} elseif ($statusFilter === 'inactive') {
    $where[] = "status = 0";
}

$whereSql = "WHERE " . implode(" AND ", $where);

// 
// COUNT QUERY (ADDED)
// 
$countSql = "SELECT COUNT(*) AS total FROM users $whereSql";
$countRes = mysqli_query($mysqli, $countSql);
$countRow = mysqli_fetch_assoc($countRes);
$totalUsers = $countRow['total'];
$totalPages = ceil($totalUsers / $limit);
// 

// Fetch users (ACTIVE FIRST, INACTIVE LAST)
$result = mysqli_query(
    $mysqli,
    "SELECT * FROM users
     $whereSql
     ORDER BY status DESC, id ASC
     LIMIT $limit OFFSET $offset"
);
?>

