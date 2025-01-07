<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

include_once '../../inc/dbmysqli.php';
include_once '../models/GuestbookMessage.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $guestbook = new GuestbookMessage($conn);

    if ($guestbook->addMessage($sender_id, $receiver_id, $message)) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        echo "Något gick fel. Försök igen senare.";
    }
}
?>