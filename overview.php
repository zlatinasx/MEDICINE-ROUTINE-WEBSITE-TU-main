<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Взимаме всички рутини
$routines = [];
$stmt = $conn->prepare("SELECT * FROM medicine_routines WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $routines[] = $r;
}

include 'header.php';
?>

<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-md">

  <?php if (empty($routines)): ?>
    <p class="text-center text-gray-600">No saved routines.</p>
  <?php else: ?>
    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-200 text-sm">
          <th class="border px-2 py-1">Medicine</th>
          <th class="border px-2 py-1">Type</th>
          <th class="border px-2 py-1">One-time</th>
          <th class="border px-2 py-1">Period</th>
          <th class="border px-2 py-1">Time</th>
          <th class="border px-2 py-1">Notes</th>
          <th class="border px-2 py-1">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($routines as $r): ?>
        <tr class="text-sm">
          <td class="border px-2 py-1"><?= htmlspecialchars($r['medicine_name']) ?></td>
          <td class="border px-2 py-1"><?= $r['scheduled_date'] ? 'One-time' : 'Recurring' ?></td>
          <td class="border px-2 py-1"><?= $r['scheduled_date'] ? date('d.m.Y', strtotime($r['scheduled_date'])) : '-' ?></td>
          <td class="border px-2 py-1"><?= $r['start_date'] ? date('d.m.Y', strtotime($r['start_date'])) . ' → ' . date('d.m.Y', strtotime($r['end_date'])) : '-' ?></td>
          <td class="border px-2 py-1"><?= $r['scheduled_time'] ? date('H:i', strtotime($r['scheduled_time'])) : ($r['scheduled_date'] ? date('H:i', strtotime($r['scheduled_date'])) : '-') ?></td>
          <td class="border px-2 py-1"><?= nl2br(htmlspecialchars($r['notes'])) ?></td>
          <td class="border px-2 py-1 space-x-1">
            <a href="edit.php?id=<?= $r['id'] ?>&return=overview.php" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-xs">Edit</a>
           <a href="delete.php?id=<?= $r['id'] ?>&return=overview.php" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>