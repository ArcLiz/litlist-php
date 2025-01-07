<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../inc/dbmysqli.php';
include_once '../controllers/AuthController.php';

$auth = new AuthController($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user_category = 3;

    if ($auth->register($username, $password, $email, $user_category)) {
        header("Location: ../index.php");
        exit;
    } else {
        echo "Registrering misslyckades!";
    }
}
?>