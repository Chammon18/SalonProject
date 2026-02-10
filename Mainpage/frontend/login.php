<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="login-page">
    <div class="login-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to Angel’s Palace</p>

        <form method="POST">
            <div class="form-group">

                <input type="email" name="email" placeholder="Email">
                <?php if ($error && $email_error) { ?>
                    <span class="text-danger"><?= $email_error ?></span>
                <?php } ?>
            </div>

            <div class="form-group">

                <input type="password" name="password" id="password" placeholder="Password">
                <span class="toggle-password" onclick="togglePassword()">👁</span>
                <?php if ($error && $password_error) { ?>
                    <span class="text-danger"><?= $password_error ?></span>
                <?php } ?>
            </div>
            <input type="hidden" name="form-sub" value="1">
            <button type="submit" class="login-btn">Login</button>

            <div class="extra-links">
                <a href="forgot_psw.php">Forgot password?</a>
                <p>Don’t have an account? <a href="register.php">Sign up</a></p>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const pass = document.getElementById("password");
        pass.type = pass.type === "password" ? "text" : "password";
    }
</script>


<?php require_once(__DIR__ . '/../../include/footer.php'); ?>