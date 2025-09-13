<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Medicine Reminder</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white-100 text-gray-800">
  <div class="max-w-4xl mx-auto p-6">
    <header class="bg-white p-4 rounded-lg shadow-md mb-6">
      <h1 class="text-2xl font-bold text-center text-black-700 mb-3">Medicine Reminder App</h1>
      <div class="flex justify-center flex-wrap gap-3 my-6">

        <a href="index.php"
           class="<?= $currentPage === 'index.php' ? 'bg-blue-600 text-white' : 'bg-white text-black' ?> font-bold px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">
           Today
        </a>

        <a href="overview.php"
           class="<?= $currentPage === 'overview.php' ? 'bg-blue-600 text-white' : 'bg-white text-black' ?> font-bold px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">
           All Routines
        </a>

        <a href="profile.php"
           class="<?= $currentPage === 'profile.php' ? 'bg-blue-600 text-white' : 'bg-white text-black' ?> font-bold px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">
           My Profile
        </a>

        <a href="change_password.php"
           class="<?= $currentPage === 'change_password.php' ? 'bg-blue-600 text-white' : 'bg-white text-black' ?> font-bold px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">
           Change Password
        </a>

        <a href="logout.php"
           class="bg-gray-300 text-black font-bold px-4 py-2 rounded-lg shadow hover:bg-gray-400 transition">
           Logout
        </a>
      </div>
