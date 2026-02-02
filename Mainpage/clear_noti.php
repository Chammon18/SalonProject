<?php
session_start();
require_once("../public/dp.php");

$user_id = (int)($_SESSION['id'] ?? 0);

$mysqli->query("
    UPDATE notifications 
    SET 
        is_read = 1,
        show_in_nav = 0
    WHERE user_id = $user_id
");

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
