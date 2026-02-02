<?php
session_start();
require_once("../public/dp.php");

$user_id = (int)$_SESSION['id'];
$note_id = (int)$_GET['id'];

$mysqli->query("
    UPDATE notifications
    SET is_read = 1,
        show_in_nav = 0
    WHERE id = $note_id
      AND user_id = $user_id
");

header("Location: notification.php");
exit;
