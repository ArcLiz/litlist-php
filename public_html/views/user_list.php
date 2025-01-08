<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}

include_once '../../inc/dbmysqli.php';
include_once '../controllers/LibraryController.php';
include '../components/editProfile_form.php';

$libraryController = new LibraryController($conn);
$topUsers = $libraryController->getTopUsersByBooks(10);

include '../components/header.php';
?>

<!-- Container -->
<main class="grow w-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex">
    <!-- Header -->
    <div class="mx-auto">
        <!-- Quick Links -->
        <div
            class="flex flex-col justify-center text-white mx-auto mt-10 p-6 md:p-10 md:mx-10">
            <div>
                <h1 class="font-semibold text-3xl md:text-center mb-4 text-teal-400 afacad">Wall of Fame <i
                        class="fa-solid fa-arrow-down text-sm"></i></h1>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 md:mx-auto gap-6 md:gap-10 mt-10">
                <?php while ($row = $topUsers->fetch_assoc()): ?>
                    <a href="library_guestview.php?user_id=<?= $row['user_id'] ?>&sort_by=title&sort_order=ASC"
                        class="block">
                        <div
                            class="border bg-white/5 border-teal-700 rounded-t-full p-4 hover:scale-105 hover:shadow hover:shadow-neutral-800">
                            <img src="<?= $row['avatar'] ? '../uploads/avatars/' . htmlspecialchars($row['avatar']) : '/assets/noprofile.jpg' ?>"
                                alt="Avatar" class="rounded-full h-[100px] w-[100px] mx-auto">
                            <h1 class="text-center text-xl text-semibold afacad text-teal-400">
                                <?= htmlspecialchars($row['display_name'] ?: $row['username']) ?>
                            </h1>
                            <p class="text-sm text-center text-neutral-400 -mt-2">
                                @<?= htmlspecialchars(strtolower($row['username'])) ?>
                            </p>
                            <p class="text-center text-teal-500 block mt-2">
                                <?= htmlspecialchars($row['book_count']) ?> böcker
                            </p>
                        </div>
                    </a>

                <?php endwhile; ?>
            </div>

        </div>
    </div>

</main>
<?php include '../components/footer.php' ?>