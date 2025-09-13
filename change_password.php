<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate form fields
    $current = trim($_POST["old_password"] ?? '');
    $new     = trim($_POST["new_password"] ?? '');
    $confirm = trim($_POST["confirm_password"] ?? '');

    // Check all fields are filled
    if ($current === '' || $new === '' || $confirm === '') {
        $error = "Please fill in all fields.";
    }
    // Check new passwords match
    elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    }
    // Check strength: at least 8 chars, one uppercase, one lowercase, one digit, one special
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $new)) {
        $error = "Password must be at least 8 characters, include uppercase & lowercase letters, a number and a special character.";
    }
    else {
        // Fetch the current hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        // Verify old password
        if (!password_verify($current, $row['password'])) {
            $error = "Current password is incorrect.";
        } else {
            // Hash and update new password
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $newHash, $_SESSION['user_id']);
            if ($upd->execute()) {
             $_SESSION['flash_message'] = "Password changed successfully. Please log in again.";
            logUserAction($conn, $_SESSION['user_id'], "Password changed.");
            session_unset();
            session_destroy();
            header("Location: login.php?message=" . urlencode("Password changed successfully. Please log in again."));
            exit();
        } else {
      $error = "An error occurred while updating your password.";
}
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    
    <h2 class="text-2xl font-bold text-center mb-6">Change Password</h2>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded relative mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded relative mb-4 text-sm">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Current Password:</label>
        <input
          type="password"
          name="old_password"
          required
          class="w-full border rounded px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">New Password:</label>
        <input
          type="password"
          name="new_password"
          pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}"
          title="At least 8 chars, include uppercase, lowercase, number & special char"
          required
          class="w-full border rounded px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Confirm New Password:</label>
        <input
          type="password"
          name="confirm_password"
          required
          class="w-full border rounded px-3 py-2"
        >
      </div>

      <button
        type="submit"
        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
      >
        Change Password
      </button>
    </form>

    <p class="mt-4 text-center text-sm">
      <a href="index.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
    </p>
  </div>
</body>
</html>