<?php
function logUserAction($conn, $userId, $action) {
    $stmt = $conn->prepare(
      "INSERT INTO activity_log (user_id, action, timestamp) VALUES (?, ?, NOW())"
    );
    $stmt->bind_param("is", $userId, $action);
    $stmt->execute();
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
function isRoutineTakenToday($conn, $routine_id) {
    $today = date('Y-m-d');

    $stmt = $conn->prepare("SELECT COUNT(*) FROM medicine_taken WHERE routine_id = ? AND taken_date = ?");
    $stmt->bind_param("is", $routine_id, $today);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_row();

    return $result[0] > 0;
}