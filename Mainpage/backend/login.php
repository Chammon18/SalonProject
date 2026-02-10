<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');

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

?>
