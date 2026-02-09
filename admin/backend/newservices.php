<?php
require_once(__DIR__ . '/../auth_check.php');
// session_start();

$error = false;
$errors = [];

// FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // GET & SANITIZE INPUTS
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration    = trim($_POST['duration'] ?? '');
    $status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    // VALIDATION

    if ($category_id === 0) {
        $error = true;
        $errors['category'] = "Category is required";
    }

    if ($name === '') {
        $error = true;
        $errors['name'] = "Service name is required";
    }

    if ($price === '' || !is_numeric($price)) {
        $error = true;
        $errors['price'] = "Valid price is required";
    }

    if ($description === '') {
        $error = true;
        $errors['description'] = "Description is required";
    }

    if ($duration === '') {
        $error = true;
        $errors['duration'] = "Duration is required";
    }


    // IMAGE UPLOAD

    $image = '';
    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = true;
            $errors['image'] = "Only JPG, PNG, WEBP allowed";
        } else {

            $upload_dir = "../admin/uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    } else {
        $error = true;
        $errors['image'] = "Service image is required";
    }


    // DUPLICATE CHECK

    if (!$error) {
        $check = $mysqli->query("
            SELECT id FROM services 
            WHERE category_id = $category_id 
            AND name = '" . $mysqli->real_escape_string($name) . "'
        ");

        if ($check->num_rows > 0) {
            $error = true;
            $errors['name'] = "This service already exists in this category";
        }
    }

    // INSERT

    if (!$error) {

        $sql = "
            INSERT INTO services 
            (category_id, name, description, price, duration, image, status)
            VALUES (
                $category_id,
                '" . $mysqli->real_escape_string($name) . "',
                '" . $mysqli->real_escape_string($description) . "',
                $price,
                '$duration',
                '$image',
                $status
            )
        ";

        if ($mysqli->query($sql)) {
            echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Service Added',
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href='services.php';
                });
            </script>";
        }
    }
}
?>

