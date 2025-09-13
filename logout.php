<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    logUserAction($conn, $userId, "User logged out");
}

session_destroy();
header("Location: login.php");
exit();
?>
