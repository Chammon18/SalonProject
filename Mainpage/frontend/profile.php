<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">My Profile</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <!-- PROFILE IMAGE -->
                    <div class="text-center mb-4">
                        <img
                            src="./profile/<?= !empty($user['profile_image']) ? $user['profile_image'] : 'default.png' ?>"
                            class="rounded-circle mb-2"
                            width="120"
                            height="120"
                            style="object-fit:cover">


                        <form method="post" enctype="multipart/form-data">
                            <input type="file" name="profile_image" class="form-control mt-2">
                            <button class="btn btn-outline-success btn-sm mt-2">Change Photo</button>
                        </form>
                    </div>

                    <!-- USER INFO (READ ONLY) -->
                    <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></div>
                    <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                    <div class="mb-3"><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></div>

                    <hr>

                    <!-- PASSWORD CHANGE -->
                    <form method="post">
                        <h6 class="mb-3">Change Password</h6>

                        <input type="password" name="old_password" class="form-control mb-2" placeholder="Old Password">
                        <input type="password" name="new_password" class="form-control mb-2" placeholder="New Password">
                        <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password">

                        <?php if ($password_error): ?>
                            <div class="text-danger mb-2"><?= $password_error ?></div>
                        <?php endif; ?>

                        <button class="btn btn-success w-100">Update Profile</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../../include/footer.php'); ?>