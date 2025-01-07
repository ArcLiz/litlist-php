<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}

include_once '../controllers/LibraryController.php';
include '../components/wishlist.php';
include '../components/editProfile_form.php';

$libraryController = new LibraryController($conn);
$topUsers = $libraryController->getTopUsersByBooks(4);

include '../components/header.php';
?>

<!-- Container -->
<main class="grow w-screen min-h-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex">
    <!-- Header -->
    <div class="mx-auto">
        <!-- Välkommen -->
        <div
            class="bg-white w-screen flex flex-col justify-center md:w-auto md:flex-row md:justify-between md:mt-24 p-10 md:rounded-2xl shadow-lg shadow-neutral-900">
            <div class="flex flex-col justify-between space-y-4 ">
                <h1 class="afacad text-3xl font-bold text-teal-400">
                    <?php
                    // Hämta aktuell tid
                    $hour = date('H'); // 24-timmarsformat (00 till 23)
                    
                    if ($hour >= 5 && $hour < 12) {
                        $greeting = "God morgon";
                    } elseif ($hour >= 12 && $hour < 18) {
                        $greeting = "God eftermiddag";
                    } else {
                        $greeting = "God kväll";
                    }

                    // Visa hälsningen
                    echo "$greeting, $username!";
                    ?>
                </h1>
                <p>Vad vill du göra just nu?</p>

                <div class="text-sm flex-col flex space-y-4">
                    <a href="user_read.php"
                        class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md text-center">Lästa
                        böcker</a>
                    <a href="library_table.php"
                        class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md text-center">Besöka mitt
                        bibliotek</a>
                    <button id="openWishlistModal"
                        class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md">Öppna Önskelista</button>
                    <button id="editProfileBtn"
                        class="mt-4 bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md">Kontoinställningar</button>
                </div>



            </div>
            <div class="mx-auto mt-4 md:mr-5">
                <img src="../assets/welcome.png" alt="" class="h-[300px]">
            </div>
        </div>

        <!-- Quick Links -->
        <div
            class="flex flex-col justify-center text-white mx-auto mt-10 p-10 mx-10">
            <div>
                <h1 class="font-semibold text-3xl md:text-center mb-4 text-teal-400 afacad">Inspiration <i
                        class="fa-solid fa-arrow-down text-sm"></i></h1>
                <h2 class="text-xl md:text-center afacad">Nedan hittar du våra populäraste användare.</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 md:mx-auto gap-10 mt-10">
                <?php while ($row = $topUsers->fetch_assoc()): ?>
                    <div
                        class="border border-teal-700 rounded-t-full p-4 hover:scale-105 hover:shadow hover:shadow-neutral-800">
                        <img src="<?= $row['avatar'] ? '../uploads/avatars/' . htmlspecialchars($row['avatar']) : '/assets/libbg.jpg' ?>"
                            alt="Avatar" class="rounded-full h-[100px] w-[100px] mx-auto">
                        <h1 class="text-center text-semibold afacad text-2xl text-teal-400">
                            <?= htmlspecialchars($row['username']) ?>
                        </h1>
                        <p class="text-sm text-center text-neutral-400 -mt-2">
                            @<?= htmlspecialchars(strtolower($row['username'])) ?>
                        </p>
                        <a href="library_guestview.php?user_id=<?= $row['user_id'] ?>&sort_by=title&sort_order=ASC"
                            class="text-center text-teal-500 hover:underline block mt-2">
                            <?= htmlspecialchars($row['book_count']) ?> böcker
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>

</main>
<?php include '../components/footer.php' ?>

<script>
    // Wishlist Modal
    const wishlistModal = document.getElementById('wishlistModal');
    const openWishlistModalBtn = document.getElementById('openWishlistModal');
    const closeWishlistModalBtn = document.getElementById('closeWishlistModal');

    openWishlistModalBtn.addEventListener('click', function () {
        wishlistModal.classList.remove('hidden');
    });
    closeWishlistModalBtn.addEventListener('click', function () {
        wishlistModal.classList.add('hidden');
    });

    // Edit Profile Modal
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');

    editProfileBtn.addEventListener('click', function () {
        editProfileModal.classList.remove('hidden');
    });
</script>