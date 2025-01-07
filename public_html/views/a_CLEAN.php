<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}

include '../components/header.php';
?>

<!-- CONTENT HERE -->

<?php include '../components/footer.php' ?>