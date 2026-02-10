<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');

$user_id = (int)$_SESSION['id'];

// 🔴 show_in_nav ခEfilter လုပ်ခE
$all_notif_res = $mysqli->query("
    SELECT * 
    FROM notifications
    WHERE user_id = $user_id
    ORDER BY created_at DESC
");


?>
