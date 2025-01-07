<?php
session_start();

include_once '../../inc/dbmysqli.php';
include_once '../controllers/AuthController.php';

$auth = new AuthController($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header("Location: ../views/home.php");
        exit;
    } else {
        echo "Fel användarnamn eller lösenord!";
    }
}
?>