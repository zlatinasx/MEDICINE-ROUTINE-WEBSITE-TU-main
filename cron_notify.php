<?php
// cron_notify.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Sofia');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$today       = date('Y-m-d');
$now         = date('Y-m-d H:i:s');
$timeNow     = date('H:i:s', strtotime('-3 minutes'));
$soonTime    = date('H:i:s', strtotime('+5 minutes'));
$windowStart = date('Y-m-d H:i:s', strtotime('-3 minutes'));
$windowEnd   = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$reset = $conn->prepare("
  UPDATE medicine_routines
  SET notified = 0
  WHERE mode = 'recurring'
    AND start_date <= ?
    AND end_date >= ?
");
$reset->bind_param('ss', $today, $today);
$reset->execute();
$sql = "
  SELECT 
    mr.id, mr.user_id, mr.medicine_name, mr.scheduled_date,
    mr.start_date, mr.end_date, mr.scheduled_time,
    mr.notified,
    u.email
  FROM medicine_routines mr
  JOIN users u ON mr.user_id = u.id
  WHERE (
    (mr.scheduled_date IS NOT NULL AND mr.scheduled_date BETWEEN ? AND ?)
    OR
    (mr.scheduled_date IS NULL
      AND mr.start_date <= ?
      AND mr.end_date >= ?
      AND mr.scheduled_time BETWEEN ? AND ?)
  )
  AND mr.notified = 0
  AND NOT EXISTS (
    SELECT 1 FROM medicine_taken mt
    WHERE mt.routine_id = mr.id AND mt.taken_date = ?
  )
";


$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sssssss',
    $windowStart, $windowEnd,
    $today, $today, $timeNow, $soonTime,
    $today // за подзаявката
);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $rid   = $row['id'];
    $uid   = $row['user_id'];
    $email = $row['email'];
    $name  = $row['medicine_name'];

    if ($row['scheduled_date']) {
        $remindTime = date('H:i', strtotime($row['scheduled_date']));
    } else {
        $remindTime = substr($row['scheduled_time'], 0, 5);
    }

    error_log("cron_notify: sending reminder for #{$rid} ({$name}) at {$remindTime} to {$email}");

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'zlatinasx@gmail.com';
        $mail->Password   = 'mrse xszx bkpe bwym';  // App password!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('zlatinasx@gmail.com', 'Medicine App');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->Subject = 'Reminder: time to take your medicine';
        $mail->Body    = "
          <p>Hello,</p>
          <p>This is a reminder that at <strong>{$remindTime}</strong> you should take:</p>
          <p><strong>{$name}</strong></p>
          <p>Best regards,<br>Your Medicine App</p>
        ";
        $mail->AltBody = "Hello,\n\nReminder: at {$remindTime} take {$name}.\n\nBest regards,\nYour Medicine App";

        $mail->send();

        logUserAction($conn, $uid, "Sent reminder for routine #{$rid} at {$now}");
        error_log("cron_notify: mail sent for routine #{$rid}");

        $update = $conn->prepare("UPDATE medicine_routines SET notified = 1 WHERE id = ?");
        $update->bind_param('i', $rid);
        $update->execute();


    } catch (Exception $e) {
        error_log("cron_notify: email error for #{$rid} to {$email}: {$mail->ErrorInfo}");
    }
}

error_log("cron_notify: completed at {$now}");