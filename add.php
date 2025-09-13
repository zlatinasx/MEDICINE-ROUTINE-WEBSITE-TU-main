<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
date_default_timezone_set('Europe/Sofia');

requireLogin();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Common fields
    $mode        = $_POST['mode'];            // 'one' или 'recurring'
    $name        = trim($_POST['medicine_name']);
    $dosage      = trim($_POST['dosage']);
    $timeOfDay   = trim($_POST['time_of_day']);
    $notes       = trim($_POST['notes']);

    // Mode-specific
    if ($mode === 'one') {
        // еднократно: само datetime-local
        $scheduledDate = $_POST['scheduled_date'];   // формата YYYY-MM-DDTHH:MM
        $startDate     = null;
        $endDate       = null;
        $scheduledTime = null;
    } else {
        // периодично: date + time
        $scheduledDate = null;
        $startDate     = $_POST['start_date'];       // YYYY-MM-DD
        $endDate       = $_POST['end_date'];         // YYYY-MM-DD
        $scheduledTime = $_POST['scheduled_time'];   // HH:MM
    }

    // Запис в базата
    $stmt = $conn->prepare("
        INSERT INTO medicine_routines
          (user_id, medicine_name, dosage, time_of_day, notes,
           mode, scheduled_date, start_date, end_date, scheduled_time, created_at, notified)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)
    ");
    $stmt->bind_param(
        'isssssssss',
        $userId,
        $name,
        $dosage,
        $timeOfDay,
        $notes,
        $mode,
        $scheduledDate,
        $startDate,
        $endDate,
        $scheduledTime
    );
    $stmt->execute();

    logUserAction(
        $conn,
        $userId,
        "Added {$mode} medicine '{$name}'"
    );

    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Add New Medicine</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4 text-center">Add New Medicine</h2>
    <form method="post" class="space-y-4">
      <!-- Mode selector -->
      <div class="flex gap-4">
        <label class="inline-flex items-center">
          <input type="radio" name="mode" value="one" checked class="form-radio">
          <span class="ml-2">One-time</span>
        </label>
        <label class="inline-flex items-center">
          <input type="radio" name="mode" value="recurring" class="form-radio">
          <span class="ml-2">Recurring</span>
        </label>
      </div>

      <!-- Common fields -->
      <div>
        <label class="block font-medium">Medicine Name:</label>
        <input type="text" name="medicine_name" required
               class="w-full border rounded p-2">
      </div>
      <div>
        <label class="block font-medium">Dosage:</label>
        <input type="text" name="dosage"
               class="w-full border rounded p-2">
      </div>
      <div>
        <label class="block font-medium">Time of Day:</label>
        <select name="time_of_day" class="w-full border rounded p-2">
          <option>Morning</option>
          <option>Afternoon</option>
          <option>Evening</option>
        </select>
      </div>

      <!-- One-time fields -->
      <div id="one-time-fields">
        <label class="block font-medium">Scheduled Date & Time:</label>
        <input type="datetime-local" name="scheduled_date"
               class="w-full border rounded p-2">
      </div>

      <!-- Recurring fields -->
      <div id="recurring-fields" class="hidden space-y-2">
        <div>
          <label class="block font-medium">Start Date:</label>
          <input type="date" name="start_date"
                 class="w-full border rounded p-2">
        </div>
        <div>
          <label class="block font-medium">End Date:</label>
          <input type="date" name="end_date"
                 class="w-full border rounded p-2">
        </div>
        <div>
          <label class="block font-medium">Scheduled Time:</label>
          <input type="time" name="scheduled_time"
                 class="w-full border rounded p-2">
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block font-medium">Notes:</label>
        <textarea name="notes" class="w-full border rounded p-2"></textarea>
      </div>

      <!-- Actions -->
      <div class="flex justify-between">
        <a href="index.php" class="text-gray-600 hover:underline">Cancel</a>
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Add
        </button>
      </div>
    </form>
  </div>

  <script>
    const oneF = document.getElementById('one-time-fields');
    const recF = document.getElementById('recurring-fields');
    document.querySelectorAll('input[name=mode]').forEach(rb => {
      rb.addEventListener('change', e => {
        if (e.target.value === 'one') {
          oneF.classList.remove('hidden');
          recF.classList.add('hidden');
        } else {
          oneF.classList.add('hidden');
          recF.classList.remove('hidden');
        }
      });
    });
  </script>
</body>
</html>