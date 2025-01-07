<?php
session_start();
require_once '../../inc/dbmysqli.php';
require_once '../models/GuestbookMessage.php';


if (isset($_SESSION['user_id']) && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $user_id = $_SESSION['user_id'];

    $guestbook = new GuestbookMessage($conn);

    $stmt = $conn->prepare("SELECT sender_id FROM guestbook_messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();

    if ($message && $message['sender_id'] == $user_id) {
        if ($guestbook->deleteMessage($message_id)) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Fel vid borttagning av meddelandet.";
        }
    } else {
        echo "Du har inte behörighet att ta bort detta meddelande.";
    }
} else {
    echo "Fel: Ingen användare inloggad eller meddelande-id saknas.";
}
?>