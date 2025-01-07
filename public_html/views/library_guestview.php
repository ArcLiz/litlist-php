<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../inc/dbmysqli.php';
include_once '../controllers/LibraryController.php';
include_once '../controllers/AuthController.php';
include_once '../models/Book.php';
include_once '../models/Wishlist.php';

// Sätt profil-ID från URL:en
$profile_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$profile_id) {
    die("Profil-ID saknas eller är ogiltigt.");
}

// Hantera sidnummer och andra parametrar
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Skapa kontroller
$libraryController = new LibraryController($conn);
$authController = new AuthController($conn);

// Hämta användarnamn för profil-ID
$profile_user = $authController->getUsernameById($profile_id);
$profile_bio = $authController->getBioById($profile_id);
$profile_avatar = $authController->getAvatarById($profile_id);
if (!$profile_user) {
    die("Användaren hittades inte. Kontrollera att user_id är korrekt: " . $profile_id);
}

// Hämta böcker baserat på profil-ID
$booksResult = $libraryController->getAllBooksByUser($profile_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);
$totalBooks = $libraryController->getTotalBooksByUser($profile_id, $searchTerm);
$totalPages = ceil($totalBooks / $limit);

// Spara böckerna i en array
$books = [];
if ($booksResult->num_rows > 0) {
    while ($row = $booksResult->fetch_assoc()) {
        $books[] = $row;
    }
}

// Skapa en instans av Wishlist
$wishlistModel = new Wishlist($conn);

// Hämta önskelistan för användarens profil
$wishlist = $wishlistModel->getWishlist($profile_id);

include '../components/header.php';
?>


<!-- CONTAINER -->
<main class="grow w-screen bg-gradient-to-b from-neutral-900 to-neutral-700">
    <div class="mx-auto bg-white p-6 max-w-[1280px]">
        <div class="flex justify-between -m-6 p-6 bg-gradient-to-b from-white to-teal-500">
            <div class="w-4/5 mx-auto">
                <img src="../uploads/avatars/<?php echo $profile_avatar ?>" alt=""
                    class="h-40 w-40 rounded-full border-4 border-teal-600 mb-4 mx-auto">
                <h1 class="afacad uppercase text-2xl text-center ml-3">
                    Välkommen till <?php echo htmlspecialchars($profile_user); ?>
                </h1>
                <p class="text-center">
                    <?php echo htmlspecialchars($profile_user); ?> har <?php echo $totalBooks ?> böcker
                    registrerade.<br><br>
                    <?php echo htmlspecialchars($profile_bio); ?>
                </p>
            </div>
            <div class="w-full grid grid-cols-2 gap-4">
                <div class="border p-4 bg-white/50 shadow-lg">
                    <h2 class="text-2xl font-bold mb-4 text-teal-500 text-center">Önskelista <i
                            class="fa-solid fa-gift pl-3 text-xl"></i></h2>
                </div>


                <?php if (empty($wishlist)): ?>
                    <div class="col-span-2">
                        <p>Du har inga böcker i din önskelista ännu.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($wishlist as $book): ?>
                        <div class="border p-4 bg-white/50 shadow-lg">
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="text-xs italic"><?php echo "av " . htmlspecialchars($book['author']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="">

        </div>
    </div>
    <div class="mx-auto bg-white p-6 max-w-[1280px] min-h-screen">

        <!-- PAGE HEADER -->
        <div class="flex justify-between items-center mb-2">


            <form method="GET" class="mb-4 flex items-center relative">
                <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>"
                    placeholder="Sök efter böcker..."
                    class="border border-gray-300 rounded-full h-10 w-48 px-4 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">

                <?php if ($searchTerm): ?>
                    <a href="library_table.php?user_id=<?= $user_id ?>" class="absolute right-3 text-red-500"><i
                            class="fa-solid fa-rotate-left"></i></a>
                <?php else: ?>
                    <button type="submit" class="absolute right-3 text-gray-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- DESKTOP VIEW -->
        <div class="overflow-x-auto hidden lg:block">
            <table class="min-w-full table-auto divide-y divide-gray-200 mb-10">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=title&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Titel</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=author&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Författare</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=genre&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Genre</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=location&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Placering</a>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">

                    <!-- BEGIN DESKTOP LOOP -->
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $row): ?>
                            <tr class="hover:bg-gray-100 cursor-pointer"
                                onclick="window.location.href='library_book.php?id=<?= $row['id'] ?>';">
                                <td class="px-6 py-2 whitespace-normal"><?= htmlspecialchars($row['title']) ?></td>
                                <td class="px-6 py-2 whitespace-normal"><?= htmlspecialchars($row['author']) ?></td>
                                <td class="px-6 py-2 whitespace-normal"><?= htmlspecialchars($row['genre']) ?></td>
                                <td class="px-6 py-2 whitespace-normal"><?= htmlspecialchars($row['location']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600">Inga böcker hittades för detta
                                användar-ID.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBILE VIEW -->
        <div class="lg:hidden mx-auto py-2 overflow-x-none w-full mb-12">
            <div class="border-b border-gray-200 spacer-y-2 divide-y divide-neutral-200">
                <!-- BEGIN MOBILE LOOP -->
                <?php if (count($books) > 0): ?>
                    <?php foreach ($books as $row): ?>
                        <div class="flex justify-between hover:bg-gray-100 cursor-pointer p-2 rounded-lg"
                            onclick="window.location.href='library_book.php?id=<?= $row['id'] ?>';">
                            <!-- Kontrollera om det finns en cover_image, annars använd en standardbild -->
                            <img src="<?= !empty($row['cover_image']) ? '../uploads/book_covers/' . htmlspecialchars($row['cover_image']) : '../uploads/book_covers/nocover.png' ?>"
                                alt="" class="h-[90px]">

                            <div class="flex flex-col text-start w-3/4">
                                <p class="text-neutral-800 pt-1"><?= htmlspecialchars($row['title']) ?></p>
                                <p class="text-xs text-neutral-700">av <?= htmlspecialchars($row['author']) ?></p>
                                <div class="flex justify-end">
                                    <button
                                        class="bg-green-300 border border-green-500 uppercase p-1 text-xs text-green-800 rounded-lg <?= empty($row['location']) ? 'hidden' : '' ?>">
                                        @ <?= $row['location'] ?>
                                    </button>
                                </div>
                                <p class="text-xs text-neutral-700 mt-auto pb-3 <?= empty($row['series']) ? 'hidden' : '' ?>">
                                    Bok <?= $row['series_number'] ?> i <?= $row['series'] ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-600">Inga böcker hittades för detta användar-ID.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?> <!-- Kontrollera om det finns mer än en sida -->
            <div class="mt-auto flex justify-center">
                <nav aria-label="Page navigation">
                    <ul class="inline-flex -space-x-px">
                        <li>
                            <a href="?page=<?= max(1, $page - 1) ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="px-3 py-2 text-sm font-medium <?= $page == 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-100' ?>"
                                aria-label="Previous" <?= $page == 1 ? 'aria-disabled="true"' : '' ?>>
                                Tillbaka
                            </a>
                        </li>

                        <li>
                            <a href="?page=1&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="mr-4 px-3 py-2 text-sm font-medium <?= $page == 1 ? 'text-blue-600 bg-blue-100' : 'text-gray-500 bg-white' ?> border border-gray-300 hover:bg-gray-100">
                                1
                            </a>
                        </li>

                        <?php
                        $start = max(2, $page - 1);
                        $end = min($totalPages - 1, $start + 2);
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li>
                                <a href="?page=<?= $i ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                    class="mr-4 px-3 py-2 text-sm font-medium <?= $page == $i ? 'text-blue-600 bg-blue-100' : 'text-gray-500 bg-white' ?> border border-gray-300 hover:bg-gray-100">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li>
                            <a href="?page=<?= $totalPages ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="mr-4 px-3 py-2 text-sm font-medium <?= $page == $totalPages ? 'text-blue-600 bg-blue-100' : 'text-gray-500 bg-white' ?> border border-gray-300 hover:bg-gray-100">
                                <?= $totalPages ?>
                            </a>
                        </li>

                        <li>
                            <a href="?page=<?= min($totalPages, $page + 1) ?>&user_id=<?= $user_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="px-3 py-2 text-sm font-medium <?= $page == $totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-100' ?>"
                                aria-label="Next" <?= $page == $totalPages ? 'aria-disabled="true"' : '' ?>>
                                Nästa
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>
    </div>
</main>

<!-- FOOTER -->
<?php include '../components/footer.php'; ?>

</body>

</html>