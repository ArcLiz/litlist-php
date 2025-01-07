<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../components/header.php';


$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$display_name = $_SESSION['display_name'];
$avatar = $_SESSION['avatar'];
$bio = $_SESSION['bio'];
$created_at = $_SESSION['created_at'];
$user_category = $_SESSION['user_category'];

include '../components/editProfile_form.php';
include '../components/wishlist.php';
include_once '../controllers/LibraryController.php';

$libraryController = new LibraryController($conn);

$wishlist = getWishlist($user_id);
$totalBooks = $libraryController->getTotalBooksByUser($user_id);

?>


<main
    class="grow w-screen min-h-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex justify-center">
    <!-- Profile Card -->
    <div class="mx-auto container">
        <div id="swap-container"
            class="flex flex-col md:flex-row bg-white md:justify-between mx-auto max-w-4xl md:h-[550px] rounded-2xl shadow-lg shadow-neutral-900 md:mt-16">
            <div id="left-section"
                class="box md:rounded-l-2xl bg-white p-8 md:w-2/3 flex flex-col justify-between space-y-5">
                <!-- PROFIL -->
                <div class="text-center">
                    <div class="flex justify-around md:justify-between items-center">
                        <div class="w-1/3 md:w-1/4">
                            <img src="../uploads/avatars/<?php echo $avatar ?>" alt=""
                                class="h-32 w-32 rounded-full border-4 border-teal-500 mb-4">
                        </div>
                        <div class="w:2/3 md:w-3/4 afacad text-center">
                            <h1 class="text-3xl font-bold text-teal-400 mb-2">
                                <?php
                                if (!empty($display_name)) {
                                    echo htmlspecialchars($display_name);
                                } else {
                                    echo htmlspecialchars($username);
                                }
                                ?>
                            </h1>

                            <?php if (!empty($display_name)): ?>
                                <p class="text-lg text-gray-700 mb-1">
                                    <span>@</span><?php echo htmlspecialchars($username); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="relative mt-10 text-start">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-book text-gray-400"></i>
                            <span class="ml-2 text-gray-400 text-sm">BIO</span>
                        </span>
                        <div
                            class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-700 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                            <p class="pl-8">
                                <?php echo !empty($bio) ? htmlspecialchars($bio) : "Ingen bio tillgänglig"; ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-start mx-4 my-5">
                        <ul class="list-disc p-4 ">
                            <li><?php echo $totalBooks ?> registrerade böcker</li>
                            <li>medlem sedan den
                                <?php echo date("j", strtotime($created_at)) . " " . strtolower(strftime('%B', strtotime($created_at))) . " " . date("Y", strtotime($created_at)); ?>
                            </li>
                            <li>
                                <?php
                                if ($_SESSION['user_category'] == 1) {
                                    echo "site-administratör";
                                } elseif ($_SESSION['user_category'] == 2) {
                                    echo "privat profil";
                                } elseif ($_SESSION['user_category'] == 3) {
                                    echo "offentlig profil";
                                } else {
                                    echo "Okänd användarkategori";
                                }
                                ?>
                            </li>
                        </ul>
                    </div>
                </div>


                <!-- Edit Profile Button -->
                <div class="md:text-start ">
                    <button id="editProfileBtn"
                        class="active:scale-90 w-full md:w-fit mt-8 border border-teal-600 bg-teal-600 text-white hover:bg-white hover:text-teal-600 py-3 px-6 uppercase rounded-3xl font-medium">
                        <i class="fa-solid fa-pen"></i> Profil
                    </button>
                </div>
            </div>

            <!-- ÖNSKELISTA -->
            <div id="right-section"
                class="md:rounded-r-2xl bg-teal-500 p-8 md:w-1/3 text-white afacad md:h-[550px] flex flex-col justify-between">
                <div>
                    <h2 class="text-3xl font-bold mb-4">Önskelista <i class="fa-solid fa-gift pl-3 text-xl"></i></h2>
                    <ul class="list-disc pl-5">
                        <?php if (empty($wishlist)): ?>
                            <li>Du har inga böcker i din önskelista ännu.</li>
                        <?php else: ?>
                            <?php foreach ($wishlist as $book): ?>
                                <li>
                                    <?php echo htmlspecialchars($book['title']) . " <br><span class='text-xs italic'> av " . htmlspecialchars($book['author']) . "</span>"; ?>
                                </li>
                            <?php endforeach; ?>

                        <?php endif; ?>

                    </ul>
                </div>
                <div class="text-end">
                    <button id="openWishlistModal"
                        class="active:scale-90 w-full md:w-fit mt-8 border border-white text-white py-3 px-6 uppercase rounded-3xl font-medium hover:bg-teal-700">Önskelista
                        <i
                            class="fa-solid fa-pen"></i></button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>

<script>
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');

    editProfileBtn.addEventListener('click', function () {
        editProfileModal.classList.remove('hidden');
    });

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
</script>