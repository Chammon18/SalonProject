<?php
require_once(__DIR__ . '/../auth_check.php');
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capacity'])) {
    $updated = 0;
    foreach ($_POST['capacity'] as $catId => $capVal) {
        $catId = (int)$catId;
        $capVal = trim($capVal);
        if ($catId <= 0) {
            continue;
        }

        if ($capVal === '') {
            $ok = $mysqli->query("UPDATE categories SET capacity = NULL WHERE id = $catId");
        } else {
            $capInt = max(0, (int)$capVal);
            $ok = $mysqli->query("UPDATE categories SET capacity = $capInt WHERE id = $catId");
        }

        if ($ok) {
            $updated++;
        } else {
            $error_msg = "Failed to update some categories. Please try again.";
        }
    }

    if (!$error_msg) {
        $success_msg = "Updated $updated categories.";
    }
}

$categories = $mysqli->query("
    SELECT id, name, incentive_percent, capacity
    FROM categories
    ORDER BY name ASC
");
?>

