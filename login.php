<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
redirectIfLoggedIn();

$error = '';

// Обработка на login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username !== '' && $password !== '') {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Защита от session fixation
                $_SESSION['user_id'] = $user['id'];
                logUserAction($conn, $user['id'], "User logged in");

                header("Location: index.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User does not exist.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4 text-sm text-center">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

        <?php if ($error): ?>
            <p class="text-red-600 mb-4 text-sm"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Username:</label>
                <input
                    type="text"
                    name="username"
                    required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Password:</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
            >
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            Don't have an account? 
            <a href="register.php" class="text-blue-600 hover:underline">Register here</a><br>
            <a href="forgot_password.php" class="text-blue-600 hover:underline">Forgot Password?</a>
        </p>
    </div>
</body>
</html>