<?php require_once("adminheader.php"); ?>


<div class="content text-white">
    <div class="d-flex text-white justify-content-between mb-3">
        <!-- <h3 class="text-white">Admin Profile</h3> -->
        <a href="admin_create.php" class="btn btn-dark"><i class="fa fa-plus"></i> Create Admin</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="card shadow border-0 rounded-4 p-4">

        <!-- PROFILE IMAGE -->
        <div class="text-center mb-4">
            <img
                src="../admin/profile/<?= !empty($user['profile_image'])
                                            ? htmlspecialchars($user['profile_image'])
                                            : 'default.png' ?>"
                class="rounded-circle mb-2"
                width="120"
                height="120"
                style="object-fit:cover">


            <form method="post" enctype="multipart/form-data" class="mt-2">
                <input type="hidden" name="action" value="profile_image">
                <input type="file" name="profile_image" class="form-control mb-2" required>
                <button class="btn btn-outline-success btn-sm ">Change Photo</button>
            </form>
        </div>

        <!-- USER INFO (READ ONLY) -->
        <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></div>
        <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>

        <hr>

        <!-- PASSWORD CHANGE -->

        <form method="post" class="mt-3">
            <input type="hidden" name="action" value="password_change">

            <small class="text-muted d-block mb-2">
                Leave password fields empty if you don’t want to change your password.
            </small>

            <input type="password" name="old_password" class="form-control mb-2" placeholder="Old Password">
            <input type="password" name="new_password" class="form-control mb-2" placeholder="New Password">
            <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password">

            <button type="submit" class="btn btn-success btn-sm">
                <i class="fa fa-save"></i> Update
            </button>
        </form>
    </div>
</div>
<?php require_once('adminfooter.php'); ?>


