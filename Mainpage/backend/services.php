<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');
require_once(__DIR__ . '/../../public/common_query.php');

// Get selected category
$cat = $_GET['category'] ?? 'all';

// Fetch all categories from DB
$cat_res = $mysqli->query("SELECT * FROM categories");
$categories = [];
while ($c = $cat_res->fetch_assoc()) {
    $categories[$c['name']] = $c['id']; // ['nail' => 1, 'eyelash' => 2, ...]
}

// Build SQL
if ($cat === 'all') {
    $sql = "SELECT * FROM services WHERE status=1 ORDER BY created_at DESC";
} elseif (isset($categories[$cat])) {
    $cat_id = $categories[$cat];
    $sql = "SELECT * FROM services WHERE status=1 AND category_id=$cat_id ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM services WHERE 0"; // invalid category
}

$res = $mysqli->query($sql);
?>
