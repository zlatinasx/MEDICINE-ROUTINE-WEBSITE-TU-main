 <?php
session_start();
require_once 'db.php';
require_once 'functions.php';
date_default_timezone_set('Europe/Sofia');
requireLogin();

$userId = $_SESSION['user_id'];

// Обработка на "Mark as Taken"
if (isset($_POST['mark_taken_id'])) {
    $medId = intval($_POST['mark_taken_id']);
    $today = date('Y-m-d');
    $now   = date('Y-m-d H:i:s');

    if (!isRoutineTakenToday($conn, $medId)) {
        $ins = $conn->prepare("INSERT INTO medicine_taken (routine_id, taken_date, taken_at) VALUES (?, ?, ?)");
        $ins->bind_param("iss", $medId, $today, $now);
        $ins->execute();
        logUserAction($conn, $userId, "Marked routine #{$medId} as taken on {$today} at {$now}");
    }

    header('Location: index.php');
    exit();
}

// Вземане на днешните лекарства
$today = date('Y-m-d');
$routines = [];

$stmt = $conn->prepare("
    SELECT * FROM medicine_routines
     WHERE user_id = ?
       AND (
           (scheduled_date IS NOT NULL AND DATE(scheduled_date) = ?)
        OR (start_date IS NOT NULL AND end_date IS NOT NULL AND start_date <= ? AND end_date >= ?)
       )
     ORDER BY COALESCE(start_date, DATE(scheduled_date)), scheduled_time
");
$stmt->bind_param("isss", $userId, $today, $today, $today);
$stmt->execute();
$res = $stmt->get_result();

while ($r = $res->fetch_assoc()) {
    $r['taken_today'] = isRoutineTakenToday($conn, $r['id']);
    $routines[] = $r;
}

$todayFormatted = date('j F Y');

// Зареждаме изгледа
include 'header.php';
include 'dashboard_view.php';