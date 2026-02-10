<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="register-page">
    <div class="register-card">
        <h2>Create Account</h2>
        <p class="subtitle">Join Angel’s Palace</p>

        <form method="POST" action="register.php" class="register-form">
            <div class="form-group floating-label">
                <input type="text" name="name" id="name"
                    value="<?= htmlspecialchars($name ?? '') ?>"
                    placeholder="Full Name">
                <?php if (!empty($name_error)): ?>
                    <span class="text-danger"><?= $name_error ?></span>
                <?php endif; ?>
            </div>


            <div class="form-group floating-label">
                <input type="email" name="email" id="email"
                    value="<?= htmlspecialchars($email ?? '') ?>" placeholder="Email">
                <?php if (!empty($email_error)): ?>
                    <span class="text-danger"><?= $email_error ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group floating-label">
                <input type="text" name="phone" id="phone"
                    value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="Phone">
                <?php if (!empty($phone_error)): ?>
                    <span class="text-danger"><?= $phone_error ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group floating-label password-group">
                <input type="password" name="password" id="password" placeholder="Password">
                <!-- <span class="toggle-password" onclick="togglePassword('password')">👁</span> -->
                <?php if (!empty($password_error)): ?>
                    <span class="text-danger"><?= $password_error ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group floating-label password-group">
                <input type="password" name="confirmpassword" id="confirmpassword" placeholder="Confirm Password">
                <!-- <span class="toggle-password" onclick="togglePassword('confirmpassword')">👁</span> -->
                <?php if (!empty($confirmpassword_error)): ?>
                    <span class="text-danger"><?= $confirmpassword_error ?></span>
                <?php endif; ?>
            </div>

            <input type="hidden" name="form-sub" value="1">

            <button type="submit" class="login-btn">Create Account</button>

            <div class="extra-links">
                <p>Already have an account?
                    <a href="login.php">Login</a>
                </p>
            </div>

        </form>



    </div>
</div>



<?php require_once(__DIR__ . '/../../include/footer.php'); ?>