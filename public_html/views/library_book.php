<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);


include_once '../../inc/dbmysqli.php';
include_once '../models/Book.php';

$book_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$book_id) {
    echo "Ingen bok vald.";
    exit;
}

$book = new Book($conn);
$book->id = $book_id;
$book->read_one();

$cover_image = $book->cover_image ? '../uploads/book_covers/' . $book->cover_image : '../uploads/book_covers/nocover.png';

include '../components/header.php';
?>

<div class="container mx-auto bg-white p-8 rounded-lg shadow-lg mt-10">
    <div class="flex items-start space-x-8">
        <img src="<?= htmlspecialchars($cover_image) ?>" alt="Bokomslag"
            class="w-1/4 h-auto object-cover rounded-lg shadow-md">
        <div class="w-2/3">
            <h1 class="text-4xl font-bold mb-4"><?= htmlspecialchars($book->title) ?></h1>
            <p class="text-gray-700 mb-2"><strong>Författare:</strong> <?= htmlspecialchars($book->author) ?></p>
            <p class="text-gray-700 mb-2"><strong>Genre:</strong> <?= htmlspecialchars($book->genre) ?></p>
            <p class="text-gray-700 mb-2"><strong>Utgivningsår:</strong> <?= htmlspecialchars($book->published_year) ?>
            </p>
            <p class="text-gray-700 mb-2"><strong>Plats:</strong> <?= htmlspecialchars($book->location) ?></p>
            <?php if ($book->series): ?>
                <p class="text-gray-700 mb-2"><strong>Serie:</strong> <?= htmlspecialchars($book->series) ?></p>
                <p class="text-gray-700 mb-2"><strong>Serienummer:</strong> <?= htmlspecialchars($book->series_number) ?>
                </p>
            <?php endif; ?>
            <p class="text-gray-700 mb-2"><strong>Beskrivning:</strong></p>
            <p class="text-gray-600 leading-relaxed mb-4"><?= nl2br(htmlspecialchars($book->description)) ?></p>
            <p class="text-gray-700 mb-2"><strong>Kommentarer:</strong></p>
            <p class="text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($book->comment)) ?></p>
        </div>
    </div>
</div>

<?php include '../components/footer.php'; ?>