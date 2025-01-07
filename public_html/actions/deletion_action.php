<?php
session_start();

include_once '../../inc/dbmysqli.php';
include_once '../models/Book.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $book = new Book($conn);
        $book->id = intval($_POST['id']);

        if ($book->delete()) {
            header("Location: ../views/library_table.php?user_id=" . $_SESSION['user_id']);
            exit();
        } else {
            echo "<script>alert('Något gick fel vid borttagning av boken.');</script>";
            header("Location: ../views/library_table.php?user_id=" . $_SESSION['user_id']);
            exit();
        }
    } else {
        echo "<script>alert('Ingen bok vald för borttagning.');</script>";
        header("Location: ../views/library_table.php?user_id=" . $_SESSION['user_id']);
        exit();
    }
}
?>