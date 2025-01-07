<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../inc/dbmysqli.php';
include_once '../controllers/LibraryController.php';
include_once '../models/Book.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

$libraryController = new LibraryController($conn);

// Fetching books based on user ID, search, and sort criteria
$booksResult = $libraryController->getAllBooksByUser($user_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);
$totalBooks = $libraryController->getTotalBooksByUser($user_id, $searchTerm);
$totalPages = ceil($totalBooks / $limit);

// Spara böckerna i en array
$books = [];
if ($booksResult->num_rows > 0) {
    while ($row = $booksResult->fetch_assoc()) {
        $books[] = $row;
    }
}

include '../components/header.php';
?>

<!-- CONTAINER -->
<main class="grow w-screen bg-gradient-to-b from-neutral-900 to-neutral-700">
    <div class="mx-auto bg-white p-6 max-w-[1280px] min-h-screen">
        <!-- PAGE HEADER -->
        <div class="flex justify-between items-center mb-2">
            <div class="flex items-center">
                <img src="../assets/shelf.svg" alt="" class="h-10">
                <h1 class="afacad uppercase text-xl text-gray-800 ml-3 hidden md:block">Dina böcker
                    (<?php echo $totalBooks ?>)</h1>
            </div>

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            *
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
                                <td class="px-6 py-2 whitespace-nowrap flex justify-end">
                                    <button id="menu-toggle-<?= $row['id'] ?>" onclick="toggleMenu(event, <?= $row['id'] ?>)"
                                        class="px-3 rounded-lg border border-white hover:border-neutral-500">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <div id="menu-<?= $row['id'] ?>"
                                        class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg">
                                        <a href="library_create.php?id=<?= $row['id'] ?>"
                                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Redigera</a>
                                        <form action="../actions/deletion_action.php" method="POST">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit"
                                                class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100"
                                                onclick="return confirm('Är du säker på att du vill ta bort denna bok?');">Ta
                                                bort</button>
                                        </form>
                                    </div>
                                </td>
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
                            <!-- MENY-TOGGLE - MOBILE BUTTON -->
                            <button id="menu-mob-toggle-<?= $row['id'] ?>" onclick="toggleMobileMenu(event, <?= $row['id'] ?>)"
                                class="h-[32px] w-[32px] flex justify-center rounded-lg border border-neutral-200 hover:border-neutral-400 items-center mt-2">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div id="menuMobile-<?= $row['id'] ?>"
                                class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg">
                                <a href="library_create.php?id=<?= $row['id'] ?>"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Redigera</a>
                                <form action="../actions/deletion_action.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100"
                                        onclick="return confirm('Är du säker på att du vill ta bort denna bok?');">Ta
                                        bort</button>
                                </form>
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

<script>
    function toggleMenu(event, id) {
        event.stopPropagation();
        const menu = document.getElementById(`menu-${id}`);
        const toggles = document.querySelectorAll(`[id^=menu-toggle-]`);

        toggles.forEach(toggle => {
            if (toggle !== event.target) {
                toggle.classList.remove('active');
            }
        });

        // Växla synligheten på menyn
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }

        // Lägg till event listener för att stänga menyn om användaren klickar utanför
        document.addEventListener('click', function closeMenu(event) {
            if (!menu.contains(event.target)) {
                menu.classList.add('hidden');
                document.removeEventListener('click', closeMenu); // Ta bort event listener efter att menyn stängts
            }
        });
    }

    function toggleMobileMenu(event, id) {
        event.stopPropagation();
        const menuMob = document.getElementById(`menuMobile-${id}`);
        const mobToggles = document.querySelectorAll(`[id^=menu-mob-toggle-]`);

        mobToggles.forEach(toggle => {
            if (toggle !== event.target) {
                toggle.classList.remove('active');
            }
        });

        // Växla synligheten på den mobila menyn
        if (menuMob.classList.contains('hidden')) {
            menuMob.classList.remove('hidden');
        } else {
            menuMob.classList.add('hidden');
        }

        // Lägg till event listener för att stänga menyn om användaren klickar utanför
        document.addEventListener('click', function closeMobileMenu(event) {
            if (!menuMob.contains(event.target)) {
                menuMob.classList.add('hidden');
                document.removeEventListener('click', closeMobileMenu); // Ta bort event listener efter att menyn stängts
            }
        });
    }

</script>

</body>

</html>