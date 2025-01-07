<?php
// Inkludera databaskopplingen
include('../../inc/dbmysqli.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $rating = $_POST['rating'];
    $date_finished = $_POST['date_finished'];

    $sql = "UPDATE library_read SET title = ?, author = ?, rating = ?, date_finished = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param('ssisi', $title, $author, $rating, $date_finished, $book_id);

    if ($stmt->execute()) {
        header('Location: /views/user_read.php');
    } else {
        echo "Det gick inte att uppdatera boken: " . $stmt->error;
    }

    // Stäng anslutningen till databasen
    $stmt->close();
    $conn->close();
}
?>