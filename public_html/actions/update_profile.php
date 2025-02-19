<?php
session_start();
include '../models/User.php';
include '../../inc/dbmysqli.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


$user = new User($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['display_name'], $_POST['bio'])) {
        $user->id = $_SESSION['user_id'];
        $user->display_name = $_POST['display_name'];
        $user->bio = $_POST['bio'];
        $user->email = $_SESSION['email'];

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $avatar = $_FILES['avatar']['name'];
            $target_dir = "../uploads/avatars/";
            $target_file = $target_dir . basename($avatar);

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                $user->avatar = $avatar;
            } else {
                echo "Failed to upload the avatar.";
                $user->avatar = $_SESSION['avatar'];
            }
        } else {
            $user->avatar = $_SESSION['avatar'];
        }

        if ($user->updateProfile()) {
            $_SESSION['display_name'] = $user->display_name;
            $_SESSION['bio'] = $user->bio;
            $_SESSION['avatar'] = $user->avatar;
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Failed to update profile.";
        }
    } else {
        echo "Display name and bio are required.";
    }
}

$conn->close();
?>