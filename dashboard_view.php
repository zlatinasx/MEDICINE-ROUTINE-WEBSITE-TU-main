
  <div class="text-center mb-6">
    <h2 class="text-xl font-bold mb-4">Today's Dashboard: <?= $todayFormatted ?></h2>
    <a href="add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      + Add New Medicine
    </a>
  </div>

  <?php if (empty($routines)): ?>
    <p class="text-center text-gray-600">No active medicines for today.</p>
  <?php else: ?>
    <ul class="space-y-4">
      <?php foreach ($routines as $routine): ?>
        <li class="p-4 rounded border <?= $routine['taken_today'] ? 'bg-green-50' : 'bg-white' ?> shadow-sm">
          <div class="flex items-center justify-between">
            <?php if (!$routine['taken_today']): ?>
              <form method="post" class="inline">
                <input type="hidden" name="mark_taken_id" value="<?= $routine['id'] ?>">
                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                  Mark as Taken
                </button>
              </form>
            <?php else: ?>
              <span class="text-green-800 font-semibold text-sm">✓ Taken Today</span>
            <?php endif; ?>

            <strong class="text-lg"><?= htmlspecialchars($routine['medicine_name']) ?></strong>

            <div class="flex gap-2">
              <a href="edit.php?id=<?= $routine['id'] ?>"
                 class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-sm">
                Edit
              </a>
              <a href="delete.php?id=<?= $routine['id'] ?>&return=index.php" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Delete</a>
            </div>
          </div>

          <div class="mt-2 text-sm text-black-700">
            <?php if ($routine['scheduled_date']): ?>
              Scheduled on <strong><?= date('d.m.Y', strtotime($routine['scheduled_date'])) ?></strong>
              at <strong><?= date('H:i', strtotime($routine['scheduled_date'])) ?></strong>
            <?php else: ?>
              From <strong><?= date('d.m.Y', strtotime($routine['start_date'])) ?></strong>
              to <strong><?= date('d.m.Y', strtotime($routine['end_date'])) ?></strong>
              at <strong><?= date('H:i', strtotime($routine['scheduled_time'])) ?></strong>
            <?php endif; ?>
          </div>

          <?php if ($routine['dosage']): ?>
            <div class="text-xs text-gray-500">Dosage: <?= htmlspecialchars($routine['dosage']) ?></div>
          <?php endif; ?>

          <?php if ($routine['notes']): ?>
            <div class="text-xs italic text-gray-500"><?= htmlspecialchars($routine['notes']) ?></div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
