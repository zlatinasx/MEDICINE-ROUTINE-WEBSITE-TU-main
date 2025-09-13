<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';
require_once 'functions.php';
redirectIfLoggedIn();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $query  = "SELECT id FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        // Generate and hash a new random password
        $new_password_plain  = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);
        $new_password_hashed = password_hash($new_password_plain, PASSWORD_DEFAULT);

        $update_query = "UPDATE users SET password='$new_password_hashed' WHERE email='$email'";
       if (mysqli_query($conn, $update_query)) {
          // Вземаме user_id за activity_log
        $userRow = mysqli_fetch_assoc($result);
          $userId = $userRow['id'];

        // Записваме действието в activity_log
        logUserAction($conn, $userId, "Requested password reset");

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'zlatinasx@gmail.com';        // your SMTP user
                $mail->Password   = 'mrse xszx bkpe bwym';          // use an App Password!
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('zlatinasx@gmail.com','Medicine App');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Password Reset';
                $mail->Body    = 'Your new password is: <strong>' . $new_password_plain . '</strong>';
                $mail->AltBody = 'Your new password is: ' . $new_password_plain;

                $mail->send();
                $response = "success|A new password has been sent to your email.";
            } catch (Exception $e) {
                $response = "error|Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $response = "error|Error updating the password in the database.";
        }
    } else {
        $response = "error|No user registered with that email address.";
    }

    echo $response;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Forgot Password</h2>
    <div id="alert" class="hidden p-4 mb-4 text-sm rounded-lg" role="alert"></div>
    <form id="forgotForm" class="space-y-5">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
        <input type="email" name="email" id="email" required
               class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400 focus:border-transparent">
      </div>
      <button type="submit"
              class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
        Send New Password
      </button>
      <a href="login.php" class="text-blue-600 hover:underline block text-center">Back to Login</a>
    </form>
  </div>

  <script>
    document.getElementById('forgotForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const alertBox = document.getElementById('alert');

      const formData = new FormData();
      formData.append('email', email);

      const res = await fetch('forgot_password.php', {
        method: 'POST',
        body: formData
      });
      const text = await res.text();
      const [status, message] = text.split('|');

      if (status === 'success') {
        alertBox.className = 'p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg';
      } else {
        alertBox.className = 'p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg';
      }
      alertBox.textContent = message;
      alertBox.classList.remove('hidden');
    });
  </script>
</body>
</html>