<?php require_once("adminheader.php"); ?>

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
<?php require_once('adminfooter.php'); ?>


