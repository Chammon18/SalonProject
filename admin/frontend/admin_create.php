<?php require_once("adminheader.php"); ?>


<div class="content">
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="card-create-admin">
            <h3><i class="fa fa-user-shield me-2"></i>Create Admin</h3>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Admin Name" value="<?= htmlspecialchars($name) ?>" required>
                    <label for="floatingName"><i class="fa fa-user me-2"></i>Name</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="admin@example.com" value="<?= htmlspecialchars($email) ?>" required>
                    <label for="floatingEmail"><i class="fa fa-envelope me-2"></i>Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" value="<?= htmlspecialchars($password) ?>" required>
                    <label for="floatingPassword"><i class="fa fa-lock me-2"></i>Password</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Phone" value="<?= htmlspecialchars($phone) ?>" required>
                    <label for="floatingPhone"><i class="fa fa-phone me-2"></i>Phone</label>
                </div>

                <button class="btn btn-create"><i class="fa fa-plus me-2"></i>Create Admin</button>
            </form>

            <a href="index.php" class="back-link"><i class="fa fa-arrow-left me-1"></i>Back to Login again</a>
        </div>
    </div>
</div>
<?php require_once('adminfooter.php'); ?>


