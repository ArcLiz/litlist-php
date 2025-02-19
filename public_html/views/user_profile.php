<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

include_once '../../inc/dbmysqli.php';
include_once '../controllers/LibraryController.php';
include_once '../controllers/AuthController.php';
include_once '../models/Book.php';
include_once '../models/Wishlist.php';
include_once '../models/GuestbookMessage.php';
include_once '../controllers/ReadController.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

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
$user_user = $authController->getUsernameById($user_id);
$user_bio = $authController->getBioById($user_id);
$user_avatar = $authController->getAvatarById($user_id);
$isPublic = $authController->showReadingHistoryPrivacyForm($user_id);
if (!$user_user) {
    die("Användaren hittades inte. Kontrollera att user_id är korrekt: " . $user_id);
}

// Hämta böcker baserat på profil-ID
$booksResult = $libraryController->getAllBooksByUser($user_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);
$totalBooks = $libraryController->getTotalBooksByUser($user_id, $searchTerm);
$totalPages = ceil($totalBooks / $limit);


// WISHLIST
$wishlistModel = new Wishlist($conn);

$wishlist = $wishlistModel->getWishlist($user_id);

// GUESTBOOK
$receiver_id = $user_id;
$guestbook = new GuestbookMessage($conn);

$messages = $guestbook->getMessagesForUser($receiver_id);

// READ BOOKS
$booksResult = $readController->getAllReadBooksByUser($user_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);
$booksInYear = $readController->getBooksReadThisYear($user_id);

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
<main class="grow flex w-screen bg-gradient-to-b from-neutral-900 to-neutral-700">
    <!-- PROFILE DETAILS CONTAINER -->
    <div class="grow mx-auto max-w-[1280px] bg-gradient-to-b from-white to-teal-500">
        <!-- Welcome, Profile Details -->
        <div class="space-y-4 md:space-y-0 md:flex md:flex-row justify-between p-6">
            <div class="md:w-4/5 mx-auto">
                <img src="../uploads/avatars/<?php echo $user_avatar ?>" alt=""
                    class="h-24 w-24 md:h-40 md:w-40 rounded-full border-4 border-teal-600 mb-4 mx-auto">
                <h1 class="uppercase text-2xl text-center ml-3">
                    Välkommen till <?php echo htmlspecialchars($user_user); ?>
                </h1>
                <p class="text-center">
                    <?php echo htmlspecialchars($user_user); ?> har <?php echo $totalBooks ?> böcker
                    registrerade.<br><br>
                    <?php echo html_entity_decode($user_bio); ?>
                </p>
            </div>
            <div class="w-full grid grid-cols-2 gap-4">
                <div class="border p-2 md:p-4 bg-white/50 shadow-lg flex justify-center items-center">
                    <h2 class="text-xl md:text-2xl font-bold text-teal-500 text-center">Inköpslista</h2>
                </div>

                <?php if (empty($wishlist)): ?>
                    <div class="col-span-2">
                        <p>Du har inga böcker i din inköpslista ännu.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($wishlist as $book): ?>
                        <div class="border p-2 md:p-4 bg-white/50 shadow-lg">
                            <h3 class="md:text-lg font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="text-xs italic"><?php echo "av " . htmlspecialchars($book['author']); ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php include '../components/wishlist.php'; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- CONTAINER GUESTBOOK / READ BOOKS -->
        <div class="border-t md:mt-6 md:pt-6 md:p-6">
            <!-- Guestbook Container-->
            <div class="bg-white/50 p-6 md:rounded-lg shadow-md md:space-x-4">
                <h1 class="text-center md:text-left text-3xl font-bold text-teal-700 whisper mb-4">
                    Gästbok
                </h1>
                <div class="md:space-x-4 flex flex-col md:flex-row">
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
                                    <?php if ($_SESSION['user_id'] == $message['sender_id'] || $_SESSION['user_id'] == $user_id): ?>
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
                            <p class="text-gray-500 text-center md:text-start">Inga meddelanden ännu <i
                                    class="fa-regular fa-face-frown"></i></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- READ BOOKS -->
            <div
                class="flex justify-center items-center md:items-start flex-col md:mt-6 bg-teal-800/30 p-3 md:rounded-lg">
                <div class="border-b-4 border-teal-700 border-lg rounded-lg px-3">
                    <?php if ($booksResult->num_rows > 0): ?>
                        <div class="book-container flex flex-wrap gap-1 pb-1">
                            <?php while ($row = $booksResult->fetch_assoc()): ?>
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
            <!-- END READ BOOKS -->
        </div>
    </div>
    </div>
</main>

<!-- FOOTER -->
<?php include '../components/footer.php'; ?>

</body>

</html>