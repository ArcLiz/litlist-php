<?php
include_once('../../inc/dbmysqli.php');
include_once('../controllers/AuthController.php');

$authController = new AuthController($conn);

if (isset($_POST['user_id']) && isset($_POST['is_public'])) {
    $user_id = $_POST['user_id'];
    $is_public = $_POST['is_public'];

    $authController->updateReadingHistoryPrivacy($user_id, $is_public);
}
?>