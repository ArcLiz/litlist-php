<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../inc/dbmysqli.php';
include_once '../controllers/LibraryController.php';
include_once '../controllers/AuthController.php';
include_once '../models/Book.php';
include_once '../models/Wishlist.php';
include_once '../models/GuestbookMessage.php';
include_once '../controllers/ReadController.php';

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
$readController = new ReadController($conn);
$libraryController = new LibraryController($conn);
$authController = new AuthController($conn);

// Hämta användarnamn för profil-ID
$profile_user = $authController->getUsernameById($profile_id);
$profile_bio = $authController->getBioById($profile_id);
$profile_avatar = $authController->getAvatarById($profile_id);
$isPublic = $authController->showReadingHistoryPrivacyForm($profile_id);
if (!$profile_user) {
    die("Användaren hittades inte. Kontrollera att user_id är korrekt: " . $profile_id);
}

// Hämta böcker baserat på profil-ID
$booksResult = $libraryController->getAllLibraryBooksByUser($profile_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);
$totalBooks = $libraryController->getTotalBooksByUser($profile_id, $searchTerm);
$totalPages = ceil($totalBooks / $limit);

// Spara böckerna i en array
$books = [];
if ($booksResult->num_rows > 0) {
    while ($row = $booksResult->fetch_assoc()) {
        $books[] = $row;
    }
}

// WISHLIST
$wishlistModel = new Wishlist($conn);

$wishlist = $wishlistModel->getWishlist($profile_id);

// GUESTBOOK
$receiver_id = $profile_id;
$guestbook = new GuestbookMessage($conn);

$messages = $guestbook->getMessagesForUser($receiver_id);

// READ BOOKS
$readBooksResult = $readController->getAllReadBooksByUser($profile_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);
$readBooksInYear = $readController->getBooksReadThisYear($profile_id);

$query = "SELECT title, date_finished FROM library_read WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$finishedBooks = [];
while ($row = $result->fetch_assoc()) {
    $finishedBooks[] = [
        'title' => $row['title'],
        'date' => $row['date_finished']
    ];
}

include '../components/header.php';
?>


<!-- CONTAINER -->
<main class="grow w-screen bg-gradient-to-b from-neutral-900 to-neutral-700">
    <!-- PROFILE DETAILS CONTAINER -->
    <div class="mx-auto bg-white max-w-[1280px] bg-gradient-to-b from-white to-teal-500">
        <!-- Welcome, Profile Details -->
        <div class="space-y-4 md:space-y-0 md:flex md:flex-row justify-between p-6">
            <div class="md:w-4/5 mx-auto">
                <img src="../uploads/avatars/<?php echo $profile_avatar ?>" alt=""
                    class="h-24 w-24 md:h-40 md:w-40 rounded-full border-4 border-teal-600 mb-4 mx-auto">
                <h1 class="uppercase text-2xl text-center ml-3">
                    Välkommen till <?php echo htmlspecialchars($profile_user); ?>
                </h1>
                <p class="text-center">
                    <?php echo htmlspecialchars($profile_user); ?> har <?php echo $totalBooks ?> böcker
                    registrerade.<br><br>
                    <?php echo htmlspecialchars($profile_bio); ?>
                </p>
            </div>
            <div class="w-full grid grid-cols-2 gap-4">
                <div class="border p-2 md:p-4 bg-white/50 shadow-lg flex justify-center items-center">
                    <h2 class="text-xl md:text-2xl font-bold text-teal-500 text-center">Inköpslista</h2>
                </div>

                <?php if (empty($wishlist)): ?>
                    <div class="col-span-2">
                        <p>Inköpslistan är tom.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($wishlist as $book): ?>
                        <div class="border p-2 md:p-4 bg-white/50 shadow-lg">
                            <h3 class="md:text-lg font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="text-xs italic"><?php echo "av " . htmlspecialchars($book['author']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- CONTAINER GUESTBOOK / READ BOOKS -->
        <div id="guestbook-container" class="border-t md:mt-6 md:pt-6 md:p-6">
            <!-- Guestbook Container-->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="bg-white/50 p-6 md:rounded-lg shadow-md md:space-x-4">
                    <h1 class="text-center md:text-left text-3xl font-bold text-teal-700 whisper mb-4">
                        Gästbok
                    </h1>
                    <div class="md:space-x-4 flex flex-col md:flex-row">
                        <!-- Skicka meddelande formulär (endast för inloggade användare) -->

                        <form method="POST" action="../actions/send_message.php"
                            class="flex md:w-1/3 pb-4 md:pb-0">
                            <textarea name="message" placeholder="Skriv här.." required
                                class="w-full p-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition ease-in-out"></textarea>
                            <input type="hidden" name="receiver_id" value="<?= $profile_id ?>">
                            <button type="submit"
                                class="py-2 px-4 bg-teal-600 text-white rounded-r-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 transition">
                                Skicka
                            </button>
                        </form>


                        <!-- Gästboksmeddelanden -->
                        <div class="guestbook-messages md:grid md:grid-cols-2 md:gap-4 md:w-2/3">
                            <?php if ($messages->num_rows > 0): ?>
                                <?php while ($message = $messages->fetch_assoc()): ?>
                                    <div
                                        class="message p-3 bg-white/70 rounded-lg shadow-md flex items-start space-x-3 relative">
                                        <img src="../uploads/avatars/<?= htmlspecialchars($message['avatar']) ?>" alt="Avatar"
                                            class="w-10 h-10 rounded-full object-cover border-2 border-teal-500 hidden md:block">
                                        <div class="">
                                            <p><strong class="text-teal-600">Hälsning från
                                                    <?= htmlspecialchars($message['username']) ?><span
                                                        class="text-neutral-800 font-semibold"></span></strong></p>
                                            <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                            <p class="text-xs text-gray-500">
                                                <em><?= date("d M Y, H:i", strtotime($message['created_at'])) ?></em>
                                            </p>
                                        </div>

                                        <!-- Visa papperskorg om inloggad användare är samma som sender_id eller profile_id -->
                                        <?php if ($_SESSION['user_id'] == $message['sender_id'] || $_SESSION['user_id'] == $profile_id): ?>
                                            <form method="POST" action="../actions/delete_message.php"
                                                class="absolute bottom-1 right-2">
                                                <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                                <button type="submit" class="text-neutral-700 text-sm hover:text-red-700">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-gray-500">Inga meddelanden ännu.</p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

            <!-- READ BOOKS (IF PUBLIC) -->
            <?php
            if ($isPublic): ?>
                <div class="flex justify-center items-center flex-col md:mt-6 bg-teal-800/30 p-3 md:rounded-lg">
                    <div class="border-b-2 border-teal-300 rounded-lg mb-2 px-4">
                        <h1 class="text-3xl font-bold text-teal-700 whisper tracking-wide">
                            Böcker <?= htmlspecialchars($profile_user) ?> läst under året.
                        </h1>
                    </div>
                    <div class="border-b-4 border-teal-700 border-lg rounded-lg px-3">
                        <?php if ($booksResult->num_rows > 0): ?>
                            <div class="book-container flex flex-wrap gap-1 pb-1">
                                <?php while ($row = $readBooksResult->fetch_assoc()): ?>
                                    <!-- Bokbakgrund baserat på rating -->
                                    <?php
                                    $rating = $row['rating'];
                                    $bgColor = '';

                                    if ($rating == 1) {
                                        $bgColor = 'bg-red-200';
                                    } elseif ($rating == 2) {
                                        $bgColor = 'bg-orange-500';
                                    } elseif ($rating == 3) {
                                        $bgColor = 'bg-neutral-300';
                                    } elseif ($rating == 4) {
                                        $bgColor = 'bg-amber-200';
                                    } elseif ($rating == 5) {
                                        $bgColor = 'bg-yellow-400';
                                    }
                                    ?>

                                    <div
                                        class="bookSmall <?php echo $bgColor; ?> border-t-2 border-r-4 border-neutral-500 p-4 rounded-md flex flex-col items-center group relative overflow-hidden transition-all duration-300 w-[50px] hover:w-[150px] group-hover:w-[220px]">
                                        <div
                                            class="bookSmall-title flex justify-center items-center mb-2 opacity-100 group-hover:opacity-0 transition-opacity duration-300">
                                            <h4 class="text-md font-semibold"><?php echo htmlspecialchars($row['title']); ?></h4>
                                        </div>

                                        <!-- Författare, Betyg och Färdigläst, gömda som standard -->
                                        <div
                                            class="book-details opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                                            <p class="font-bold">Titel:</p>
                                            <p><?php echo htmlspecialchars($row['title']); ?></p>
                                            <p class="font-bold">Författare: </p>
                                            <p><?php echo htmlspecialchars($row['author']); ?></p>
                                            <p class="font-bold">Betyg: </p>
                                            <p><?php echo $row['rating']; ?>/5</p>
                                            <p class="font-bold">Färdigläst: </p>
                                            <p><?php echo $row['date_finished']; ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>Du har inte lagt till några böcker ännu.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <p></p>
            <?php endif; ?>
            <!-- END READ BOOKS -->
        </div>
    </div>


    <!-- LIBRARY -->
    <div class="mx-auto bg-white p-6 max-w-[1280px] min-h-screen">

        <!-- PAGE HEADER -->
        <div class="flex justify-between items-center mb-2">
            <form id="search-form" method="GET" class="mb-4 flex items-center relative">
                <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>"
                    placeholder="Sök efter böcker..."
                    class="border border-gray-300 rounded-full h-10 w-48 px-4 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($profile_id) ?>">

                <?php if ($searchTerm): ?>
                    <a href="library_guestview.php?user_id=<?= $profile_id ?>" class="absolute right-3 text-red-500"><i
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
                                href="?page=<?= $page ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=title&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Titel</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=author&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Författare</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=genre&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Genre</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a
                                href="?page=<?= $page ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=location&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>">Placering</a>
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
                            <a href="?page=<?= max(1, $page - 1) ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="px-3 py-2 text-sm font-medium <?= $page == 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-100' ?>"
                                aria-label="Previous" <?= $page == 1 ? 'aria-disabled="true"' : '' ?>>
                                Tillbaka
                            </a>
                        </li>

                        <li>
                            <a href="?page=1&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="mr-4 px-3 py-2 text-sm font-medium <?= $page == 1 ? 'text-blue-600 bg-blue-100' : 'text-gray-500 bg-white' ?> border border-gray-300 hover:bg-gray-100">
                                1
                            </a>
                        </li>

                        <?php
                        $start = max(2, $page - 1);
                        $end = min($totalPages - 1, $start + 2);
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li>
                                <a href="?page=<?= $i ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                    class="mr-4 px-3 py-2 text-sm font-medium <?= $page == $i ? 'text-blue-600 bg-blue-100' : 'text-gray-500 bg-white' ?> border border-gray-300 hover:bg-gray-100">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li>
                            <a href="?page=<?= $totalPages ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
                                class="mr-4 px-3 py-2 text-sm font-medium <?= $page == $totalPages ? 'text-blue-600 bg-blue-100' : 'text-gray-500 bg-white' ?> border border-gray-300 hover:bg-gray-100">
                                <?= $totalPages ?>
                            </a>
                        </li>

                        <li>
                            <a href="?page=<?= min($totalPages, $page + 1) ?>&user_id=<?= $profile_id ?>&search=<?= urlencode($searchTerm) ?>&sort=<?= $sortBy ?>&order=<?= $sortOrder ?>"
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

<script>
    // Funktion för att hämta URL-parametrar
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // Kontrollera om "search" finns i URL:en
    const searchParam = getUrlParameter('search');
    const guestbook = document.getElementById('guestbook-container');
    const readbooks = document.getElementById('readbooks-container');

    // Om "search" finns och har ett värde, dölj sektionerna
    if (searchParam && searchParam.trim() !== '') {
        guestbook.classList.add('hidden');
        readbooks.classList.add('hidden');
    } else {
        // Visa sektionerna om "search" inte finns
        guestbook.classList.remove('hidden');
        readbooks.classList.remove('hidden');
    }
</script>

<!-- FOOTER -->
<?php include '../components/footer.php'; ?>

</body>

</html>