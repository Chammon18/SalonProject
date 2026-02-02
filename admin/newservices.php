<?php
// session_start();
require_once("../public/dp.php");
require_once("adminheader.php");

$error = false;
$errors = [];

// FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =========================
    // GET & SANITIZE INPUTS
    // =========================
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration    = trim($_POST['duration'] ?? '');
    $status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    // =========================
    // VALIDATION
    // =========================
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

    // =========================
    // IMAGE UPLOAD
    // =========================
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

    // =========================
    // DUPLICATE CHECK
    // =========================
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

    // =========================
    // INSERT
    // =========================
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

<!-- =========================
     HTML FORM
========================= -->

<div class="content">
    <h3 class="text-white mb-4">Services Management</h3>

    <div class="card">
        <div class="card-body">

            <form method="POST" enctype="multipart/form-data">

                <!-- CATEGORY -->
                <div class="mb-3">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php
                        $cats = $mysqli->query("SELECT * FROM categories");
                        while ($c = $cats->fetch_assoc()):
                        ?>
                            <option value="<?= $c['id'] ?>">
                                <?= ucfirst($c['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <span class="text-danger"><?= $errors['category'] ?? '' ?></span>
                </div>

                <!-- SERVICE NAME -->
                <div class="mb-3">
                    <label>Service Name</label>
                    <input type="text" name="name" class="form-control">
                    <span class="text-danger"><?= $errors['name'] ?? '' ?></span>
                </div>

                <!-- PRICE -->
                <div class="mb-3">
                    <label>Price</label>
                    <input type="text" name="price" class="form-control">
                    <span class="text-danger"><?= $errors['price'] ?? '' ?></span>
                </div>

                <!-- DESCRIPTION -->
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                    <span class="text-danger"><?= $errors['description'] ?? '' ?></span>
                </div>

                <!-- DURATION -->
                <div class="mb-3">
                    <label>Duration</label>
                    <input type="time" name="duration" class="form-control" step="60">
                    <span class="text-danger"><?= $errors['duration'] ?? '' ?></span>
                </div>

                <!-- IMAGE -->
                <div class="mb-3">
                    <label>Service Image</label>
                    <input type="file" name="image" class="form-control">
                    <span class="text-danger"><?= $errors['image'] ?? '' ?></span>
                </div>

                <!-- STATUS -->
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <!-- SUBMIT -->
                <button class="btn btn-success">Save Service</button>
                <a href="services.php" class="btn btn-dark">Back</a>

            </form>

        </div>
    </div>
</div>