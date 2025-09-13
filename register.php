<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';
require_once 'functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateRandomPassword($length = 10) {
    return substr(str_shuffle(
       'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()'
    ), 0, $length);
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $age      = trim($_POST['age']);

    if ($username && $email && $age) {
        $stmt = $conn->prepare(
          "SELECT id FROM users WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $plain  = generateRandomPassword();
            $hashed = password_hash($plain, PASSWORD_DEFAULT);

            $ins = $conn->prepare(
              "INSERT INTO users
               (username,email,age,password)
               VALUES(?,?,?,?)"
            );
            $ins->bind_param("ssis",
              $username, $email, $age, $hashed
            );

            if ($ins->execute()) {
                // *** SMTP: App Password ***
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'zlatinasx@gmail.com';
                    $mail->Password   = 'mrse xszx bkpe bwym';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('zlatinasx@gmail.com','Medicine App');
                    $mail->addAddress($email,$username);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Medicine App Password';
                    $mail->Body    = "
                      <p>Hello {$username},</p>
                      <p>Your temporary password is: <strong>{$plain}</strong></p>
                      <p><a href='login.php'>Click here to login</a></p>
                    ";
                    $mail->AltBody = "Hello {$username},\n
                    Your temporary password is: {$plain}\n
                    Login: login.php";

                    $mail->send();
                    $success = "Registration successful! Temporary password sent to your email.";
                    $newUserId = $conn->insert_id;

                    logUserAction($conn, $newUserId, "User registered");

                } catch (Exception $e) {
                    $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Error during registration. Please try again.";
            }
        } else {
            $error = "Username is already taken.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold text-center mb-6">User Registration</h2>

    <?php if ($error): ?>
  <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded text-sm mb-4">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>
    <?php if ($success): ?>
  <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded text-sm mb-4">
    <?= htmlspecialchars($success) ?>
  </div>
<?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Username:</label>
        <input type="text" name="username" required
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email:</label>
        <input type="email" name="email" required
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Age:</label>
        <input type="number" name="age" min="1" max="120" required
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
      </div>
      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
        Register
      </button>
    </form>

    <p class="mt-4 text-center text-sm">
      Already have an account? 
      <a href="login.php" class="text-blue-600 hover:underline">Log in here</a>.
    </p>
  </div>
</body>
</html>