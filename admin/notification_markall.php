<?php
require_once "../public/dp.php";

if (!empty($_POST['notif_ids'])) {
    $ids = array_map('intval', $_POST['notif_ids']);
    $idList = implode(',', $ids);

    $mysqli->query("
        UPDATE notifications
        SET is_read = 1
        WHERE id IN ($idList)
    ");
}

header("Location: notification.php");
exit;
