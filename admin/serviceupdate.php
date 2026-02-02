<?php
require_once("../public/dp.php");
require_once("../public/common_query.php");
require_once("adminheader.php");

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
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: 'Status changes successfully',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "services.php";
                });
            </script>

<?php }
    }
} ?>

<div class="content">
    <div class="d-flex justify-content-end align-items-center mb-2">
        <a href="services.php" class="btn btn-dark rounded "> Back </a>
    </div>
    <div class="container-fluid">
        <div class="d-flex justify-content-between text-white">
            <h1>Service Update</h1>
        </div>

        <div class="d-flex justify-content-center">
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="serviceupdate.php?id=<?= $id ?>">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name"
                                value="<?= htmlspecialchars($name) ?>" required>

                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($description) ?></textarea>

                            <label>Price</label>
                            <input type="text" class="form-control" name="price"
                                value="<?= htmlspecialchars($price) ?>" required>

                            <label>Duration</label>
                            <input type="text" class="form-control" name="duration"
                                value="<?= htmlspecialchars($duration) ?>" required>

                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" <?= ($status == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($status == 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>

                            <?php if ($error && $status_error): ?>
                                <small class="text-danger"><?= $status_error ?></small>
                            <?php endif; ?>

                            <input type="hidden" name="update" value="1">
                            <button class="btn btn-primary mt-3">Update</button>
                        </form>



                    </div>

                </div>
            </div>
        </div>

    </div>