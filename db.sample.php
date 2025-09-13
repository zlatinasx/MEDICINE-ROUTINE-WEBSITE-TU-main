
<?php
// db.sample.php — пример, потребителят да го копира до db.php и да попълни креденшъли
$DB_HOST   = 'localhost';
$DB_USER   = 'your_user';
$DB_PASS   = 'your_password';
$DB_NAME   = 'medicine_routine';
$DB_CHARSET= 'utf8mb4';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$conn->set_charset($DB_CHARSET);
?>