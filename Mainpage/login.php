<?php
session_start();
require_once("../public/dp.php");

$error = false;
$email = $email_error = $password = $password_error = "";

if (isset($_POST["form-sub"]) && ($_POST['form-sub']) == 1) {
    $email = $mysqli->real_escape_string($_POST['email']);
    $password = $mysqli->real_escape_string($_POST['password']);

    // email validation
    if ($email == "") {
        $error = true;
        $email_error = "Email is required";
    }
    if ($password == "") {
        $error = true;
        $password_error = "password is required";
    }
    if (!$error) {

        $sql = "SELECT * FROM `users` where email='$email'";
        $result = $mysqli->query($sql);
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            if (password_verify($password, $data["password"])) {
                $_SESSION['id'] = $data['id'];
                $_SESSION['name'] = $data['name'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['role'] = $data['role'];
                $_SESSION['phone'] = $data['phone'];

                header("Location:index.php?success=Login success");
                exit;
            } else {
                $error = true;
                $password_error = "Password does not match with this email";
            }
        } else {
            $error = true;
            $email_error = "Email is not register";
        }
    }
}
require_once("../include/header.php");

?>

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


<?php require_once("../include/footer.php") ?>