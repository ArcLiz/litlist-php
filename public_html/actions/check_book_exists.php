<?php
session_start();
include_once '../../inc/dbmysqli.php';
include_once '../models/Book.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $user_id = $_POST['user_id'] ?? $_SESSION['user_id'] ?? null; // Hämta user_id från POST eller session

    if ($title && $user_id) {
        // Check if book exists for the specific user
        try {
            $book = new Book($conn);
            $result = $book->checkBookExists($title, $user_id);

            if ($result) {
                echo json_encode([
                    'exists' => true,
                    'title' => $result['title'],
                    'author' => $result['author']
                ]);
            } else {
                echo json_encode(['exists' => false]);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Ett fel inträffade: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Ogiltig förfrågan: Saknar titel eller användar-ID']);
    }
}
?>