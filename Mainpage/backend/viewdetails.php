<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');
require_once(__DIR__ . '/../../public/common_query.php');

// Validate service ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: services.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch service info
$res = $mysqli->query("
    SELECT s.*, c.name AS category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = $id AND s.status = 1
");

if (!$res || $res->num_rows === 0) {
    echo "<h3 class='text-center mt-5'>Service not found</h3>";
    exit;
}

$service = $res->fetch_assoc();

// Fetch related services only active ones
$related_res = $mysqli->query("
    SELECT *
    FROM services
    WHERE category_id = {$service['category_id']}
      AND id != {$service['id']}
      AND status = 1
    LIMIT 4
");

$related_services = [];
while ($r = $related_res->fetch_assoc()) {
    $related_services[] = $r;
}
?>
