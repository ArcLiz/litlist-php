<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

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


<!-- Trigger Button -->
<button id="openWishlistModal" class="bg-teal-500 text-white py-2 px-4 rounded-md">Öppna Önskelista</button>

<script>
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