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

<!-- CONTAINER -->
<main class="grow bg-gradient-to-b from-neutral-900 to-neutral-700">
    <div class="md:mx-auto bg-white md:max-w-[1280px] lg:mt-10 bg-gradient-to-b from-white to-teal-500">
        <!-- Image and Title info -->
        <div class="p-6 flex flex-col items-center justify-center lg:flex-row lg:justify-between lg:items-center">
            <img src="<?= htmlspecialchars($cover_image) ?>" alt="Bokomslag"
                class="w-1/2 md:w-1/3 lg:1/4 h-auto object-cover rounded-lg shadow-lg shadow-neutral-800">
            <div class="text-center lg:w-3/4">
                <h1 class="text-lg md:text-2xl lg:text-3xl playwrite font-bold text-neutral-900 mt-4">
                    <?= htmlspecialchars($book->title) ?>
                </h1>
                <h2 class="font-semibold text-neutral-800 playwrite md:text-lg lg:text-xl">av
                    <?= htmlspecialchars($book->author) ?>
                </h2>
                <h3 class="text-neutral-800 text-sm md:text-md lg:text-lg uppercase afacad">
                    <?php if ($book->series): ?>
                        Bok
                        <?= htmlspecialchars($book->series_number) ?>
                        i
                        <?= htmlspecialchars($book->series) ?>
                    <?php endif; ?>
                </h3>
                <div class="hidden lg:block bg-white/50 mx-6 mt-10 p-6 text-neutral-800 afacad text-center">
                    <p class="leading-relaxed mb-4"><?= nl2br(htmlspecialchars($book->description)) ?></p>
                </div>
            </div>

        </div>
    </div>
    <div class="mx-auto p-6 max-w-[1280px] afacad text-center lg:hidden">
        <p class="text-white leading-relaxed mb-4"><?= nl2br(htmlspecialchars($book->description)) ?></p>
    </div>
</main>
<?php include '../components/footer.php'; ?>