<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}

require_once('../models/Book.php');
require_once('../../inc/dbmysqli.php');

$book = new Book($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book->title = $_POST['title'];
    $book->author = $_POST['author'];
    $book->genre = $_POST['genre'];
    $book->published_year = $_POST['published_year'];
    $book->description = $_POST['description'];
    $book->comment = $_POST['comment'];
    $book->location = $_POST['location'];
    $book->series = $_POST['series'];
    $book->series_number = $_POST['series_number'];
    $book->user_id = $_SESSION['user_id'];
    $book->household_id = $_SESSION['household_id'] ?? null;


    // COVER UPLOAD
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['cover_image']['tmp_name'];
        $fileName = time() . "_" . $_FILES['cover_image']['name'];
        $uploadPath = '../uploads/book_covers/' . $fileName;

        // Validate file type
        $fileType = mime_content_type($fileTmpPath);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // Check if the file is a valid image
        if (in_array($fileType, $allowedTypes)) {
            // Convert to WebP
            $image = null;
            if ($fileType === 'image/jpeg') {
                $image = imagecreatefromjpeg($fileTmpPath);
            } elseif ($fileType === 'image/png') {
                $image = imagecreatefrompng($fileTmpPath);
            } elseif ($fileType === 'image/gif') {
                $image = imagecreatefromgif($fileTmpPath);
            }

            if ($image) {
                $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                $webpFilePath = '../uploads/book_covers/' . $webpFileName;

                // Save image as WebP
                imagewebp($image, $webpFilePath);
                imagedestroy($image);

                $book->cover_image = $webpFileName; // Set the cover image property to the WebP file name
            }
        } else {
            $error = "Only image files (JPEG, PNG, GIF) are allowed.";
        }
    } else {
        if (isset($_POST['existing_cover_image']) && !empty($_POST['existing_cover_image'])) {
            $book->cover_image = $_POST['existing_cover_image'];
        }
    }


    // Create or update book based on whether ID is provided
    if (isset($_POST['book_id']) && !empty($_POST['book_id'])) {
        $book->id = $_POST['book_id'];
        if ($book->update()) {
            header('Location: library_table.php');
            exit();
        } else {
            $error = "Error updating book. Please try again.";
        }
    } else {
        // Create new book
        if ($book->create()) {
            header('Location: library_table.php');
            exit();
        } else {
            $error = "Error adding book. Please try again.";
        }
    }
}

// Check if editing a book
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $book->id = $_GET['id'];
    $book->read_one();
}

// Check if the title is passed via URL
if (isset($_GET['title'])) {
    $book->title = htmlspecialchars($_GET['title']);
}

include '../components/header.php';
?>

<div class="mx-auto p-6 bg-white shadow-lg lg:mt-10 rounded-lg">
    <h1 class="text-2xl font-bold mb-6"><?= isset($book->id) ? 'Redigera' : 'Lägg till' ?></h1>
    <?php if (isset($error)) { ?>
        <p class="text-red-500"><?= $error ?></p>
    <?php } ?>
    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- Hidden input for book ID if editing -->
        <input type="hidden" name="book_id" value="<?= isset($book->id) ? $book->id : ''; ?>">

        <!-- Hidden input for existing cover image -->
        <input type="hidden" name="existing_cover_image"
            value="<?= isset($book->cover_image) ? $book->cover_image : ''; ?>">

        <!-- Hidden input för household_id -->
        <input type="hidden" name="household_id"
            value="<?= isset($book->household_id) ? $book->household_id : ($_SESSION['household_id'] ?? ''); ?>">



        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Titel</label>
            <input type="text" name="title" id="title" value="<?= isset($book->title) ? $book->title : ''; ?>"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                required>
        </div>

        <div>
            <label for="author" class="block text-sm font-medium text-gray-700">Författare</label>
            <input type="text" name="author" id="author" value="<?= isset($book->author) ? $book->author : ''; ?>"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                required>
        </div>
        <!-- Genre / Published Row-->
        <div class="flex justify-between items-center">
            <div class="w-2/3">
                <label for="genre" class="block w-2/3 text-sm font-medium text-gray-700">Genre</label>
                <input type="text" name="genre" id="genre" value="<?= isset($book->genre) ? $book->genre : ''; ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                    required>
            </div>

            <div class="w-1/3 pl-4">
                <label for="published_year" class="block text-sm font-medium text-gray-700">Utgiven</label>
                <input type="number" name="published_year" id="published_year"
                    value="<?= isset($book->published_year) ? $book->published_year : ''; ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                    required>
            </div>
        </div>
        <!-- Series / Number -->
        <div class="flex justify-between">
            <div class="w-3/4">
                <label for="series" class="block text-sm font-medium text-gray-700">Serie</label>
                <input type="text" name="series" id="series" value="<?= isset($book->series) ? $book->series : ''; ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <div class="w-1/4 pl-4">
                <label for="series_number" class="block text-sm font-medium text-gray-700">serie #</label>
                <input type="number" name="series_number" id="series_number"
                    value="<?= isset($book->series_number) ? $book->series_number : ''; ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>
        </div>

        <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Utlånad till (valfritt)</label>
            <input type="text" name="location" id="location"
                value="<?= isset($book->location) ? $book->location : ''; ?>"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Beskrivning</label>
            <textarea name="description" id="description"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                rows="4"><?= isset($book->description) ? $book->description : ''; ?></textarea>
        </div>

        <div>
            <label for="comment" class="block text-sm font-medium text-gray-700">Egen kommentar</label>
            <textarea name="comment" id="comment"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                rows="3"><?= isset($book->comment) ? $book->comment : ''; ?></textarea>
        </div>

        <div>
            <label for="cover_image" class="block text-sm font-medium text-gray-700">Omslagsbild (valfritt)</label>
            <input type="file" name="cover_image" id="cover_image"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                accept="image/*" onchange="loadImage(event)">

            <?php if (isset($book->cover_image) && !empty($book->cover_image)) { ?>
                <p class="mt-2 text-sm text-gray-500 text-center">Nuvarande omslagsbild:</p>
                <img src="../uploads/book_covers/<?= $book->cover_image ?>" alt="Cover Image"
                    class="max-w-[120px] h-auto rounded-md mx-auto" id="currentImage">

            <?php } else { ?>
                <p class="mt-2 text-sm text-red-500 text-center">Nuvarande omslagsbild saknas</p>
                <img src="../uploads/book_covers/nocover.png" alt="No Cover Image"
                    class="max-w-[120px] h-auto rounded-md mx-auto">
            <?php } ?>
        </div>


        <button type="submit"
            class="mt-4 block w-full py-2 rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
            <?= isset($book->id) ? 'Update Book' : 'Add Book' ?>
        </button>
    </form>
</div>

<script>
    let imgElement = null;
    let canvas = document.getElementById('imageCanvas');
    let ctx = canvas.getContext('2d');
    let currentRotation = 0;

    function loadImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            imgElement = new Image();
            imgElement.onload = function () {
                canvas.width = imgElement.width;
                canvas.height = imgElement.height;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(imgElement, 0, 0);
            };
            imgElement.src = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    function rotateImage(degrees) {
        if (imgElement) {
            currentRotation += degrees;
            currentRotation = currentRotation % 360;

            canvas.width = imgElement.width;
            canvas.height = imgElement.height;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate((currentRotation * Math.PI) / 180);
            ctx.drawImage(imgElement, -imgElement.width / 2, -imgElement.height / 2);
            ctx.restore();

            const rotatedImage = canvas.toDataURL('image/webp');
            document.getElementById('rotated_cover_image').value = rotatedImage;
        }
    }

</script>

<?php include '../components/footer.php'; ?>