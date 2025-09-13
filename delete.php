<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

// 1) Ако идва POST – изтриваме и се връщаме към подадената страница
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $delId = intval($_POST['id']);
    $returnPage = isset($_POST['return']) ? $_POST['return'] : 'index.php';

    $stmt = $conn->prepare("DELETE FROM medicine_routines WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delId, $userId);
    $stmt->execute();

    logUserAction($conn, $userId, "Deleted medicine routine #$delId");

    header("Location: $returnPage");
    exit();
}

// 2) Ако идва GET – показваме форма за потвърждение
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$delId = intval($_GET['id']);
$returnPage = isset($_GET['return']) ? $_GET['return'] : 'index.php';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title>Confirm Delete</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow text-center">
    <h2 class="text-xl font-bold mb-4">Delete Medicine Routine</h2>
    <p class="mb-6">Are you sure you want to delete this medicine?</p>
    <form method="post" class="flex justify-center gap-4">
      <input type="hidden" name="id" value="<?= $delId ?>">
      <input type="hidden" name="return" value="<?= htmlspecialchars($returnPage) ?>">
      <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Yes, delete!</button>
      <a href="<?= htmlspecialchars($returnPage) ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
    </form>
  </div>
</body>
</html>
