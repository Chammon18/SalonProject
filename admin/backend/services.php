<?php
require_once(__DIR__ . '/../auth_check.php');
require_once("../public/common_query.php");

// Filters
$categoryFilter = $_GET['category'] ?? '';
$statusFilter   = $_GET['status'] ?? '';

// 
// PAGINATION (ADDED)
// 
$limit = 3; // services per page
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max(1, $page);
$offset = ($page - 1) * $limit;
// 

// Build WHERE clause
$where = [];

if (!empty($categoryFilter)) {
    $safeCategory = mysqli_real_escape_string($mysqli, $categoryFilter);
    $where[] = "categories.name = '$safeCategory'";
}

if ($statusFilter === 'active') {
    $where[] = "services.status = 1";
} elseif ($statusFilter === 'inactive') {
    $where[] = "services.status = 0";
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// 
// COUNT QUERY (ADDED)
// 
$countSql = "
    SELECT COUNT(*) AS total
    FROM services
    LEFT JOIN categories ON services.category_id = categories.id
    $whereSql
";
$countRes = $mysqli->query($countSql);
$totalRow = $countRes->fetch_assoc();
$totalServices = $totalRow['total'];
$totalPages = ceil($totalServices / $limit);


// Main query (ACTIVE FIRST)
$sql = "
    SELECT
        services.id,
        services.name AS service_name,
        services.image,
        services.description,
        services.price,
        services.duration,
        services.status,
        categories.name AS category_name
    FROM services
    LEFT JOIN categories ON services.category_id = categories.id
    $whereSql
    ORDER BY services.status DESC, services.created_at DESC
    LIMIT $limit OFFSET $offset
";

$res = $mysqli->query($sql);

// Categories for filter dropdown
$catRes = $mysqli->query("SELECT name FROM categories ORDER BY name ASC");
?>

