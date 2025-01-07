<?php
include_once '../../inc/dbmysqli.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$title = $_POST['title'];
$author = $_POST['author'];
$rating = $_POST['rating'];
$date_finished = !empty($_POST['date_finished']) ? $_POST['date_finished'] : date('Y-m-d'); // Sätt dagens datum om inget datum anges

$sql = "INSERT INTO library_read (user_id, title, author, rating, date_finished) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issis", $user_id, $title, $author, $rating, $date_finished);


if ($stmt->execute()) {
    echo "<p class='text-green-500'>Boken har lagts till!</p>";
} else {
    echo "<p class='text-red-500'>Något gick fel. Försök igen senare.</p>";
}

$stmt->close();
$conn->close();

header("Location: ../views/user_read.php");
exit;
?>