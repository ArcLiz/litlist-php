<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../inc/dbmysqli.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$conn = new mysqli($host, $user, $password, $dbname);

$user_id = $_SESSION['user_id'];

function getWishlist($user_id)
{
    global $conn;

    $sql = "SELECT id, title, author FROM wishlist_books WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $wishlist = [];
    while ($row = $result->fetch_assoc()) {
        $wishlist[] = $row;
    }

    return $wishlist;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO wishlist_books (user_id, title, author) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $author);

        if ($stmt->execute()) {
            header("Location: ../views/user_profile.php?success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    if ($_POST['action'] === 'update') {
        $book_id = $_POST['book_id'];
        $stmt = $conn->prepare("UPDATE wishlist_books SET title = ?, author = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $author, $book_id, $user_id);

        if ($stmt->execute()) {
            header("Location: ../views/user_profile.php?updated=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    if ($_POST['action'] === 'delete') {
        $book_id = $_POST['book_id'];
        $stmt = $conn->prepare("DELETE FROM wishlist_books WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $book_id, $user_id);

        if ($stmt->execute()) {
            header("Location: ../views/user_profile.php?deleted=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$wishlist = getWishlist($user_id);
?>

<!-- Wishlist Modal -->
<div id="wishlistModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-10">
    <div class="bg-white p-4 rounded-lg shadow-md w-96">
        <div class="flex justify-between items-start">
            <h2 class="text-xl font-bold mb-4 text-teal-500">Din Önskelista</h2>
            <button type="button" id="closeWishlistModal"
                class="text-gray-800 hover:text-red-700 py-1 px-2 rounded-md"><i
                    class="fa-solid fa-rectangle-xmark"></i></button>
        </div>

        <ul class="p-2 border rounded mb-5">
            <?php if (empty($wishlist)): ?>
                <li>Du har inga böcker i din önskelista ännu.</li>
            <?php else: ?>
                <?php foreach ($wishlist as $book): ?>
                    <li class="border rounded my-2 p-2 text-sm">
                        <?php echo htmlspecialchars($book['title']) . " av " . htmlspecialchars($book['author']); ?>
                        <br>
                        <div class="text-right mt-2">
                            <button class="text-red-800" onclick="deleteBook(<?php echo $book['id']; ?>)"><i
                                    class="fa-solid fa-trash"></i></button>
                            <button class="text-blue-800"
                                onclick="showUpdateForm(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title']); ?>', '<?php echo htmlspecialchars($book['author']); ?>')"><i
                                    class="fa-solid fa-gear"></i></button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <hr>

        <!-- Formulär för att lägga till bok i önskelistan -->
        <form action="" method="POST" class="mt-4">
            <h2 class="text-xl font-bold mb-4 text-teal-500">Lägg till</h2>
            <input type="hidden" name="action" value="add">
            <div class="mb-4">
                <label for="title" class="block text-gray-700">Boktitel:</label>
                <input type="text" name="title" id="title" class="border rounded w-full py-2 px-3" required>
            </div>
            <div class="mb-4">
                <label for="author" class="block text-gray-700">Författare:</label>
                <input type="text" name="author" id="author" class="border rounded w-full py-2 px-3" required>
            </div>
            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md">Lägg till i
                Önskelistan</button>
        </form>

        <!-- Formulär för att uppdatera bok -->
        <form id="updateForm" action="" method="POST" class="hidden mt-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="book_id" id="updateBookId">
            <div class="mb-4">
                <label for="updateTitle" class="block text-gray-700">Boktitel:</label>
                <input type="text" name="title" id="updateTitle" class="border rounded w-full py-2 px-3" required>
            </div>
            <div class="mb-4">
                <label for="updateAuthor" class="block text-gray-700">Författare:</label>
                <input type="text" name="author" id="updateAuthor" class="border rounded w-full py-2 px-3" required>
            </div>
            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md">Uppdatera
                bok</button>
        </form>
    </div>
</div>

<script>
    const closeWishlistModal = document.getElementById('closeWishlistModal');

    closeWishlistModal.addEventListener('click', function () {
        document.getElementById('wishlistModal').classList.add('hidden');
    });

    function deleteBook(bookId) {
        if (confirm("Är du säker på att du vill ta bort denna bok från önskelistan?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'delete';
            form.appendChild(input);
            const bookInput = document.createElement('input');
            bookInput.type = 'hidden';
            bookInput.name = 'book_id';
            bookInput.value = bookId;
            form.appendChild(bookInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function showUpdateForm(bookId, title, author) {
        document.getElementById('updateBookId').value = bookId;
        document.getElementById('updateTitle').value = title;
        document.getElementById('updateAuthor').value = author;
        document.getElementById('updateForm').classList.remove('hidden');
    }
</script>