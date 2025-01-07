<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$display_name = $_SESSION['display_name'];
$avatar = $_SESSION['avatar'];
$bio = $_SESSION['bio'];
$created_at = $_SESSION['created_at'];
$user_category = $_SESSION['user_category'];

include_once '../../inc/dbmysqli.php';
include_once '../components/editProfile_form.php';
include_once '../controllers/LibraryController.php';
include_once '../models/Wishlist.php';

$libraryController = new LibraryController($conn);
$totalBooks = $libraryController->getTotalBooksByUser($user_id);

// Skapa en instans av Wishlist
$wishlistModel = new Wishlist($conn);

// Hämta önskelistan för användarens profil
$wishlist = $wishlistModel->getWishlist($user_id);

include '../components/header.php';

?>

<main
    class="grow w-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex justify-center">
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
                                <?php
                                $date = new DateTime($created_at);
                                echo $date->format('j F Y'); // Formaterar till exempel "7 januari 2025"
                                ?>
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
                <div class="flex justify-end">
                    <button id="openWishlistModal"
                        class="active:scale-90 w-full md:w-fit mt-8 border border-teal-600 bg-white text-teal-600 hover:bg-teal-500 hover:text-white hover:border-white py-3 px-6 uppercase rounded-3xl font-medium">Öppna
                        Önskelista</button>
                </div>
            </div>
</main>

<!-- WISHLIST MODAL TEST -->
<div id="wishlistModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-10">
    <div class="bg-white p-4 rounded-lg shadow-md w-96">
        <div class="flex justify-between items-start">
            <h2 class="text-xl font-bold mb-4 text-teal-500">Din Önskelista</h2>
            <button type="button" id="closeWishlistModal" class="text-gray-800 hover:text-red-700 py-1 px-2 rounded-md">
                <i class="fa-solid fa-rectangle-xmark"></i>
            </button>
        </div>
        <ul id="wishlist" class="p-2 border rounded mb-5">
            <!-- Dynamic Wishlist -->
        </ul>
        <hr>

        <!-- Formulär för att lägga till/redigera bok -->
        <form id="wishlistForm" class="mt-4">
            <h2 id="formTitle" class="text-xl font-bold mb-4 text-teal-500">Lägg till</h2>
            <input type="hidden" name="book_id" id="bookId">
            <div class="mb-4">
                <label for="title" class="block text-gray-700">Boktitel:</label>
                <input type="text" id="title" class="border rounded w-full py-2 px-3" required>
            </div>
            <div class="mb-4">
                <label for="author" class="block text-gray-700">Författare:</label>
                <input type="text" id="author" class="border rounded w-full py-2 px-3" required>
            </div>
            <div class="flex space-x-2">
                <button type="button" id="submitWishlistForm"
                    class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md">
                    Lägg till
                </button>

                <!-- Lägg till knappen för att återställa formuläret -->
                <button type="button" id="resetFormButton"
                    class="bg-gray-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md hidden">
                    Återställ
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../components/footer.php'; ?>

<script>
    //Profile
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');

    editProfileBtn.addEventListener('click', function () {
        editProfileModal.classList.remove('hidden');
    });

    //Wishlist test
    document.addEventListener('DOMContentLoaded', () => {
        const wishlistModal = document.getElementById('wishlistModal');
        const openWishlistModal = document.getElementById('openWishlistModal');
        const closeWishlistModal = document.getElementById('closeWishlistModal');
        const wishlistForm = document.getElementById('wishlistForm');
        const formTitle = document.getElementById('formTitle');
        const submitWishlistForm = document.getElementById('submitWishlistForm');
        const wishlistContainer = document.getElementById('wishlist');
        const bookIdInput = document.getElementById('bookId');
        const titleInput = document.getElementById('title');
        const authorInput = document.getElementById('author');
        const resetFormButton = document.getElementById('resetFormButton');  // Ny knapp för att återställa formuläret

        // Öppna och stäng modalen
        openWishlistModal.addEventListener('click', () => {
            fetchWishlist();
            wishlistModal.classList.remove('hidden');
        });

        closeWishlistModal.addEventListener('click', () => {
            wishlistModal.classList.add('hidden');
            resetForm();
        });

        // Hämta önskelista från API
        function fetchWishlist() {
            fetch('../actions/wishlist-api.php')
                .then(response => response.json())
                .then(data => {
                    wishlistContainer.innerHTML = '';
                    data.forEach(book => {
                        wishlistContainer.innerHTML += `
                    <li id="book-${book.id}" class="border rounded my-2 p-2 text-sm text-black">
                        <strong>${book.title}</strong> av ${book.author}
                        <div class="text-right mt-2">
                            <button class="text-red-800" onclick="deleteBook(${book.id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <button class="text-blue-800" onclick="editBook(${book.id}, '${book.title}', '${book.author}')">
                                <i class="fa-solid fa-gear"></i>
                            </button>
                        </div>
                    </li>`;
                    });
                });
        }

        // Lägg till eller uppdatera bok
        submitWishlistForm.addEventListener('click', () => {
            const bookId = bookIdInput.value;
            const title = titleInput.value;
            const author = authorInput.value;

            if (!title || !author) return alert('Titel och författare krävs.');

            const action = bookId ? 'update' : 'add';
            const body = new URLSearchParams({ action, book_id: bookId, title, author });

            fetch('../actions/wishlist-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            })
                .then(response => response.json())
                .then(() => {
                    fetchWishlist();
                    resetForm();
                });
        });

        window.editBook = (bookId, title, author) => {
            bookIdInput.value = bookId;
            titleInput.value = title;
            authorInput.value = author;
            formTitle.textContent = 'Redigera';

            resetFormButton.classList.remove('hidden');
        };

        window.deleteBook = (bookId) => {
            if (!confirm('Är du säker på att du vill ta bort den här boken?')) return;

            fetch('../actions/wishlist-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'delete', book_id: bookId })
            })
                .then(response => response.json())
                .then(() => fetchWishlist());
        };

        function resetForm() {
            wishlistForm.reset();
            bookIdInput.value = '';
            formTitle.textContent = 'Lägg till';
            resetFormButton.classList.add('hidden');
        }

        resetFormButton.addEventListener('click', () => {
            resetForm();
        });
    });
</script>