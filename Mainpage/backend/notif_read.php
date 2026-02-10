<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');

$user_id = $_SESSION['id'] ?? 0;
if ($user_id) {
    $mysqli->query("UPDATE notifications SET is_read=1 WHERE user_id=$user_id AND is_read=0");
}
