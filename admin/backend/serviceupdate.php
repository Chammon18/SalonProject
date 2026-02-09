<?php
require_once(__DIR__ . '/../auth_check.php');
require_once("../public/common_query.php");

$error = false;
$status = "";
$status_error = "";

// 1️⃣ Check ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: servicelist.php?error=Invalid ID");
    exit;
}

$id = (int) $_GET['id'];

// 2️⃣ Fetch service
$res = selectData("services", $mysqli, "id=$id");

if (!$res || $res->num_rows === 0) {
    header("Location: servicelist.php?error=ID not found");
    exit;
}

$row = $res->fetch_assoc();
$name = $row["name"];
$description = $row["description"];
$price = $row["price"];
$duration = $row["duration"];
$status = $row['status'];

// Update
if (isset($_POST['update']) && $_POST['update'] == "1") {
    $name = $_POST["name"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $duration = $_POST["duration"];
    $status = $_POST['status'];

    if (!in_array($status, ['0', '1'], true)) {
        $error = true;
        $status_error = "Please select valid status";
    }

    if (!$error) {
        $update = updateData(
            "services",
            $mysqli,
            ['name' => $name, 'description' => $description, 'price' => $price, 'duration' => $duration, 'status' => $status],
            "id=$id"
        );

        if ($update) {
?>

