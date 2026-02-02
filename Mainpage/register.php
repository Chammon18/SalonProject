<?php require_once("../public/dp.php");
require_once("../public/common_query.php");

function emailunique($value, $mysqli)
{
    $sql = "SELECT COUNT(id) as count FROM `users` WHERE email='$value'";
    $res = $mysqli->query($sql);
    $data = $res->fetch_assoc();
    return $data['count'] ?? 0;
}


$error = false;
$name = $name_error = $email = $email_error = $phone = $phone_error = $password = $password_error = $confirmpassword = $confirmpassword_error = "";

if (isset($_POST["form-sub"]) && ($_POST["form-sub"]) == 1) {
    $name = $mysqli->real_escape_string($_POST["name"]);
    $email = $mysqli->real_escape_string($_POST["email"]);
    $password = $mysqli->real_escape_string($_POST["password"]);
    $phone = $mysqli->real_escape_string($_POST["phone"]);
    $confirmpassword = $mysqli->real_escape_string($_POST["confirmpassword"]);

    // name validation
    if (strlen($name) === 0) {
        $error = true;
        $name_error = 'Name is required';
    } else if (strlen($name) < 5) {
        $error = true;
        $name_error = "Name chracter must be less than 5";
    } else if (strlen($name) > 50) {
        $error = true;
        $name_error = "Name Chracter must be unique";
    }

    //  email validation 
    $email_check = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (strlen($email) === 0) {
        $error = true;
        $email_error = "Email is required";
    } else if (!preg_match($email_check, $email)) {
        $error = true;
        $email_error = "Invalid email format.";
    } else if (emailunique($email, $mysqli) > 0) {
        $error = true;
        $email_error = "This email is already register";
    }

    // $phone_pattern = '/^[0-9]{7,11}$/';
    if (empty($phone)) {
        $error = true;
        $phone_error = "Phone number is required";
    }

    // password
    $password_check = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    if (strlen($password) === 0) {
        $error = true;
        $password_error = "Password is required";
    } else if (!preg_match($password_check, $password)) {
        $error = true;
        $password_error = "❌ Password does not meet requirements.";
    } else if ($password !== $confirmpassword) {
        $error = true;
        $confirmpassword_error = "Passport do not match";
    }

    if (!$error) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $sql = "INSERT INTO users(name,email,password,role,phone)
                VALUES ('$name','$email','$hash','user','$phone')";
            $mysqli->query($sql);
            header("Location: login.php?success=Register success");
            exit();
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $error = true;
                $phone_error = "Phone number or email already exists.";
            } else {
                throw $e; // other unexpected errors
            }
        }
    }
}
require_once("../include/header.php");
?>

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



<?php require_once("../include/footer.php") ?>