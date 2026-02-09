<?php require_once("adminheader.php"); ?>


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
<?php require_once('adminfooter.php'); ?>


