<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
requireLogin();

// Вземаме ID от GET
if (!isset($_GET['id'])) {
    $return = isset($_GET['return']) ? $_GET['return'] : 'index.php';
    header("Location: $return");
    exit();
}
$id = intval($_GET['id']);
$userId = $_SESSION['user_id'];

// Зареждаме рутината
$stmt = $conn->prepare("
    SELECT * 
      FROM medicine_routines
     WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$routine = $stmt->get_result()->fetch_assoc();

if (!$routine) {
    $return = isset($_GET['return']) ? $_GET['return'] : 'index.php';
    header("Location: $return");
    exit();
}

// Обработваме POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode        = $_POST['mode'];            // 'one' или 'recurring'
    $name        = trim($_POST['medicine_name']);
    $dosage      = trim($_POST['dosage']);
    $timeOfDay   = trim($_POST['time_of_day']);
    $notes       = trim($_POST['notes']);

    if ($mode === 'one') {
        $scheduledDate = $_POST['scheduled_date']; // datetime-local
        $startDate     = null;
        $endDate       = null;
        $scheduledTime = null;
    } else {
        $scheduledDate = null;
        $startDate     = $_POST['start_date'];     // date
        $endDate       = $_POST['end_date'];       // date
        $scheduledTime = $_POST['scheduled_time']; // time
    }

    $upd = $conn->prepare("
        UPDATE medicine_routines
           SET medicine_name  = ?,
               dosage         = ?,
               time_of_day    = ?,
               notes          = ?,
               mode           = ?,
               scheduled_date = ?,
               start_date     = ?,
               end_date       = ?,
               scheduled_time = ?
         WHERE id = ? AND user_id = ?
    ");
    $upd->bind_param(
        "sssssssssii",
        $name,
        $dosage,
        $timeOfDay,
        $notes,
        $mode,
        $scheduledDate,
        $startDate,
        $endDate,
        $scheduledTime,
        $id,
        $userId
    );
    $upd->execute();

    logUserAction($conn, $userId, "Edited {$mode} routine #{$id}");

    $returnPage = $_POST['return'] ?? 'index.php';
    header("Location: $returnPage");
    exit();
}

// Подготвяме флаговете за формата
$isOne = $routine['mode'] === 'one';
$isRec = $routine['mode'] === 'recurring';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Edit Medicine Routine</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Edit Medicine Routine</h2>
    <form method="post" class="space-y-4">
      <!-- Hidden return -->
      <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return'] ?? 'index.php') ?>">

      <!-- Mode -->
      <div class="flex gap-4">
        <label class="inline-flex items-center">
          <input type="radio" name="mode" value="one" <?= $isOne ? 'checked' : '' ?>>
          <span class="ml-2">One-time</span>
        </label>
        <label class="inline-flex items-center">
          <input type="radio" name="mode" value="recurring" <?= $isRec ? 'checked' : '' ?>>
          <span class="ml-2">Recurring</span>
        </label>
      </div>

      <!-- Name -->
      <div>
        <label class="block font-medium">Medicine Name:</label>
        <input type="text" name="medicine_name"
               value="<?= htmlspecialchars($routine['medicine_name']) ?>"
               required class="w-full border p-2 rounded">
      </div>

      <!-- Dosage -->
      <div>
        <label class="block font-medium">Dosage:</label>
        <input type="text" name="dosage"
               value="<?= htmlspecialchars($routine['dosage']) ?>"
               class="w-full border p-2 rounded">
      </div>

      <!-- Time of Day -->
      <div>
        <label class="block font-medium">Time of Day:</label>
        <select name="time_of_day" class="w-full border p-2 rounded">
          <?php foreach (['Morning','Afternoon','Evening'] as $opt): ?>
            <option value="<?= $opt ?>" <?= $routine['time_of_day'] === $opt ? 'selected' : '' ?>>
              <?= $opt ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- One-time -->
      <div id="one-fields" class="<?= $isOne ? '' : 'hidden' ?>">
        <label class="block font-medium">Scheduled Date &amp; Time:</label>
        <input type="datetime-local" name="scheduled_date"
               value="<?= htmlspecialchars(str_replace(' ', 'T', $routine['scheduled_date'])) ?>"
               class="w-full border p-2 rounded">
      </div>

      <!-- Recurring -->
      <div id="rec-fields" class="<?= $isRec ? '' : 'hidden' ?> space-y-2">
        <div>
          <label class="block font-medium">Start Date:</label>
          <input type="date" name="start_date"
                 value="<?= htmlspecialchars($routine['start_date']) ?>"
                 class="w-full border p-2 rounded">
        </div>
        <div>
          <label class="block font-medium">End Date:</label>
          <input type="date" name="end_date"
                 value="<?= htmlspecialchars($routine['end_date']) ?>"
                 class="w-full border p-2 rounded">
        </div>
        <div>
          <label class="block font-medium">Scheduled Time:</label>
          <input type="time" name="scheduled_time"
                 value="<?= htmlspecialchars($routine['scheduled_time']) ?>"
                 class="w-full border p-2 rounded">
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block font-medium">Notes:</label>
        <textarea name="notes" class="w-full border p-2 rounded"><?= htmlspecialchars($routine['notes']) ?></textarea>
      </div>

      <!-- Buttons -->
      <div class="flex justify-between">
        <a href="<?= htmlspecialchars($_GET['return'] ?? 'index.php') ?>" class="text-gray-600 hover:underline">Cancel</a>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>

  <script>
    const oneF = document.getElementById('one-fields'),
          recF = document.getElementById('rec-fields');
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