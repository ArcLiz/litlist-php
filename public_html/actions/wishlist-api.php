<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../inc/dbmysqli.php';
include '../models/Wishlist.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Du måste vara inloggad."]);
    exit();
}

$conn = new mysqli($host, $user, $password, $dbname);
$wishlist = new Wishlist($conn);
$user_id = $_SESSION['user_id'];

// GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $books = $wishlist->getWishlist($user_id);
    echo json_encode($books);
    exit();
}

// WRITE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        if ($wishlist->addBook($user_id, $title, $author)) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Kunde inte lägga till boken."]);
        }
        exit();
    }

    // UPDATE
    if ($action === 'update') {
        $book_id = $_POST['book_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        if ($wishlist->updateBook($user_id, $book_id, $title, $author)) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Kunde inte uppdatera boken."]);
        }
        exit();
    }

    // DELETE
    if ($action === 'delete') {
        $book_id = $_POST['book_id'] ?? null;
        if ($wishlist->deleteBook($user_id, $book_id)) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Kunde inte ta bort boken."]);
        }
        exit();
    }
}

http_response_code(400);
echo json_encode(["error" => "Ogiltig förfrågan."]);
