<?php
require_once(__DIR__ . '/../../public/dp.php');          // your database connection
require '../vendor/autoload.php';  // Composer autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $mysqli->real_escape_string($_POST['email']);

    // Check if email exists
    $result = $mysqli->query("SELECT id FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save token and expiry in DB
        $mysqli->query("UPDATE users SET reset_token='$token', token_expiry='$expiry' WHERE email='$email'");

        $resetLink = "http://localhost/SalonProject/reset_password.php?token=$token";

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com';       // your Gmail
            $mail->Password = 'your_app_password';          // Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'Salon Project');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click this link to reset your password: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            echo "Check your email for the reset link!";
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found!";
    }
}


?>
