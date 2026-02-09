<?php
session_start();
require_once("../public/dp.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$admin_id = (int)$_SESSION['admin_id'];

$result = mysqli_query(
    $mysqli,
    "SELECT status FROM users 
     WHERE id=$admin_id AND role='admin'
     LIMIT 1"
);

$admin = mysqli_fetch_assoc($result);

// 🔴 FORCE LOGOUT IF INACTIVE
if (!$admin || $admin['status'] !== 'active') {
    session_unset();
    session_destroy();
    header("Location: index.php?inactive=1");
    exit;
}

