<?php
require_once(__DIR__ . '/../auth_check.php');

$ids = $_POST['notif_ids'] ?? [];
if (!is_array($ids)) {
    $ids = [];
}
$ids = array_values(array_filter(array_map('intval', $ids)));

if (empty($ids)) {
    header("Location: notification.php?error=no_selection");
    exit;
}

$idList = implode(',', $ids);
$mysqli->query("UPDATE notifications SET is_read = 1 WHERE id IN ($idList)");

header("Location: notification.php?success=read_selected");
exit;
