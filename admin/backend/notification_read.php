<?php
require_once(__DIR__ . '/../auth_check.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $mysqli->query("UPDATE notifications SET is_read = 1 WHERE id = $id");
}

header("Location: notification.php");
exit;
