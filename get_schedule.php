<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

date_default_timezone_set('Europe/Sofia');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    echo json_encode(['error' => 'Invalid user_id']);
    exit;
}

$now = new DateTime();
$today = $now->format('Y-m-d');
$timeNow = $now->format('H:i:s');
$windowStart = $now->modify('-3 minutes')->format('H:i:s');
$windowEnd = $now->modify('+8 minutes')->format('H:i:s');

// ONE-TIME
$sqlOneTime = "
    SELECT id, medicine_name, scheduled_date
    FROM medicine_routines
    WHERE user_id = ?
      AND mode = 'one'
      AND DATE(scheduled_date) = ?
      AND TIME(scheduled_date) BETWEEN ? AND ?
      AND NOT EXISTS (
        SELECT 1 FROM medicine_taken
         WHERE routine_id = medicine_routines.id
           AND taken_date = ?
      )
    LIMIT 1
";
$stmt = $conn->prepare($sqlOneTime);
$stmt->bind_param('issss', $user_id, $today, $windowStart, $windowEnd, $today);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

if ($data) {
    echo json_encode($data);
    exit;
}

// RECURRING
$sqlRecurring = "
    SELECT id, medicine_name, scheduled_time, start_date, end_date
    FROM medicine_routines
    WHERE user_id = ?
      AND mode = 'recurring'
      AND ? BETWEEN start_date AND end_date
      AND scheduled_time BETWEEN ? AND ?
      AND NOT EXISTS (
        SELECT 1 FROM medicine_taken
         WHERE routine_id = medicine_routines.id
           AND taken_date = ?
           AND TIME(taken_at) = scheduled_time
      )
    LIMIT 1
";
$stmt = $conn->prepare($sqlRecurring);
$stmt->bind_param('issss', $user_id, $today, $windowStart, $windowEnd, $today);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

if ($data) {
    // комбинираме датата с часа
    $scheduledDateTime = $today . ' ' . $data['scheduled_time'];
    echo json_encode([
        'id' => $data['id'],
        'medicine_name' => $data['medicine_name'],
        'scheduled_date' => $scheduledDateTime
    ]);
    exit;
}

// No upcoming dose found
echo json_encode(['message' => 'No dose found']);