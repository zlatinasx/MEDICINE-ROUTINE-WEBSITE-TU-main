<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Log function
function logActivity($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}

// Load current user data
$stmt = $conn->prepare("SELECT username, email, age FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die("User not found.");

// Handle updates
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = trim($_POST["username"]);
    $new_age      = trim($_POST["age"]);

    if (empty($new_username)) {
        $error = "Username cannot be empty.";
    } elseif (!filter_var($new_age, FILTER_VALIDATE_INT) || $new_age < 1 || $new_age > 120) {
        $error = "Invalid age.";
    } else {
        $update = $conn->prepare("UPDATE users SET username = ?, age = ? WHERE id = ?");
        $update->bind_param("sii", $new_username, $new_age, $user_id);
        if ($update->execute()) {
            $success = "Profile updated successfully.";
            $_SESSION['user_username'] = $new_username;
            $_SESSION['user_age']      = $new_age;
            $user['username'] = $new_username;
            $user['age']      = $new_age;
            logActivity($conn, $user_id, "Updated profile");
        } else {
            $error = "Error while updating.";
        }
    }
}

// Load last 10 actions
$logs = [];
$log_stmt = $conn->prepare("SELECT action, timestamp FROM activity_log WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
$log_stmt->bind_param("i", $user_id);
$log_stmt->execute();
$log_result = $log_stmt->get_result();

while ($row = $log_result->fetch_assoc()) {
    $actionText = $row['action'];
    if (preg_match('/#(\d+)/', $actionText, $m)) {
        $mid = intval($m[1]);
        $mstmt = $conn->prepare("SELECT medicine_name FROM medicine_routines WHERE id = ?");
        $mstmt->bind_param("i", $mid);
        $mstmt->execute();
        $mrow = $mstmt->get_result()->fetch_assoc();
        if ($mrow) {
            $actionText = str_replace("#{$mid}", '"' . htmlspecialchars($mrow['medicine_name']) . '"', $actionText);
        }
    }
    $logs[] = [
        'timestamp' => $row['timestamp'],
        'action'    => $actionText
    ];
}

include 'header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md">

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
   <form method="post" class="space-y-4 mb-6">
    <div>
        <label class="block text-sm font-medium mb-1">Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
               required class="w-full border rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Age:</label>
        <input type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>"
               min="1" max="120" required class="w-full border rounded px-3 py-2">
    </div>
    <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Save Changes
    </button>
</form>
    <hr class="my-6">

    <h3 class="text-xl font-semibold mb-3">Activity Log</h3>
    <?php if ($logs): ?>
        <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
            <?php foreach ($logs as $log): ?>
                <li><?= htmlspecialchars($log['timestamp']) ?> — <?= htmlspecialchars($log['action']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-gray-500 text-sm">No logged actions.</p>
    <?php endif; ?>
</div>