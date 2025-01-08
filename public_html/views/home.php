<?php
session_start();

include_once '../../inc/dbmysqli.php';
include_once '../controllers/LibraryController.php';
include '../components/editProfile_form.php';

if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}

$libraryController = new LibraryController($conn);
$topUsers = $libraryController->getTopUsersByBooks(4);

include '../components/header.php';
?>

<!-- Container -->
<main class="grow min-h-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex">
    <!-- Header -->
    <div class="mx-auto">
        <!-- Välkommen -->
        <div
            class="bg-white flex flex-col justify-center md:w-auto md:flex-row md:justify-between md:mt-24 p-10 md:rounded-2xl shadow-lg shadow-neutral-900">
            <div class="flex flex-col justify-between space-y-4 ">
                <h1 class="afacad text-3xl font-bold text-teal-400">
                    <?php
                    // Hämta aktuell tid
                    $hour = date('H');

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
                    <!-- Den här knappen öppnar modalen -->
                    <?php include '../components/wishlist.php'; ?>
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
            class="flex flex-col justify-center text-white mx-auto mt-10 p-6 md:p-10 md:mx-10">
            <div>
                <h1 class="font-semibold text-3xl md:text-center mb-4 text-teal-400 afacad">Inspiration <i
                        class="fa-solid fa-arrow-down text-sm"></i></h1>
                <h2 class="text-xl md:text-center afacad">Nedan hittar du våra populäraste användare.</h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 md:mx-auto gap-6 md:gap-10 mt-10">
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

<script>
    // Edit Profile Modal
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');

    editProfileBtn.addEventListener('click', function () {
        editProfileModal.classList.remove('hidden');
    });
</script>