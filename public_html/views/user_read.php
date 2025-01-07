<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../inc/dbmysqli.php';
include_once '../controllers/ReadController.php';
include_once '../controllers/AuthController.php';

$user_id = $_SESSION['user_id']; // Hämta inloggad användares id
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 25;
$offset = ($page - 1) * $limit;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_finished';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$readController = new ReadController($conn);
$authController = new AuthController($conn);

// Hämta alla lästa böcker
$booksResult = $readController->getAllReadBooksByUser($user_id, $offset, $limit, $searchTerm, $sortBy, $sortOrder);

// Hämta det totala antalet böcker för paginering
$totalBooks = $readController->getTotalReadBooksByUser($user_id, $searchTerm);
$totalPages = ceil($totalBooks / $limit);

$booksInYear = $readController->getBooksReadThisYear($user_id);
$booksInMonth = $readController->getBooksReadThisMonth($user_id);
$favoriteAuthor = $readController->getFavoriteAuthor($user_id);
$averageRating = $readController->getAverageRating($user_id);

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

$isPublic = $authController->showReadingHistoryPrivacyForm($user_id);

include '../components/header.php';
?>

<!-- CONTENT HERE -->
<main class="grow w-screen min-h-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex justify-center">
    <div class="bg-white p-6 min-h-screen w-full md:w-2/3">
        <div>
            <label for="reading-history-checkbox">Visa läshistorik offentligt</label>
            <input type="checkbox" id="reading-history-checkbox" <?php echo $isPublic ? 'checked' : ''; ?>>
        </div>

        <!-- Formulär för att lägga till ny bok -->
        <div class="mb-6 shadow-lg rounded-lg border p-6">
            <h3 class="text-xl font-semibold mb-4">Lägg till en läst bok</h3>

            <form action="../components/add_read_book.php" method="POST" class="space-y-4 mx-auto ">
                <div class="flex-col md:flex md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <!-- Boktitel -->
                    <div class="">
                        <input type="text" id="title" name="title" placeholder="Boktitel"
                            class="input w-full p-2 border border-gray-300 rounded-md focus:outline-teal-500"
                            required>
                    </div>

                    <!-- Författare -->
                    <div class="">
                        <input type=" text" id="author" name="author" placeholder="Författare"
                            class="input w-full p-2 border border-gray-300 rounded-md focus:outline-teal-500"
                            required>
                    </div>

                    <!-- Betyg (Stjärnor istället för siffror) -->
                    <div class="">
                        <div class="flex space-x-1">
                            <select id="rating" name="rating"
                                class="input w-full p-2 border border-gray-300 rounded-md focus:outline-teal-500">
                                <option value="1">⭐</option>
                                <option value="2">⭐⭐</option>
                                <option value="3">⭐⭐⭐</option>
                                <option value="4">⭐⭐⭐⭐</option>
                                <option value="5">⭐⭐⭐⭐⭐</option>
                            </select>
                        </div>
                    </div>

                    <!-- Datum när boken blev färdigläst -->
                    <div class="flex flex-col space-y-2">
                        <input type="date" id="date_finished" name="date_finished"
                            class="input w-full p-2 border border-gray-300 rounded-md focus:outline-teal-500">
                    </div>

                    <!-- Skicka-knapp -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="btn bg-teal-500 text-white py-2 px-4 rounded-md hover:bg-teal-600 transition w-full">
                            Lägg till bok
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-around text-sm uppercase text-neutral-900 border-b my-6">
            <div class="mb-3">
                <p class="text-gray-700">Antal lästa böcker under <?php echo date('Y'); ?>:
                    <?php echo $booksInYear; ?>
                </p>
            </div>

            <div class="mb-3">
                <p class="text-gray-700">Antal lästa böcker i <?php echo date('F'); ?>:
                    <?php echo $booksInMonth; ?>
                </p>
            </div>

            <div class="mb-3">
                <p class="text-gray-700">Genomsnittligt betyg:
                    <?php echo $averageRating; ?> /
                    5
                </p>
            </div>
        </div>
        <div
            class="flex flex-col-reverse md:flex md:flex-row-reverse justify-center items-center md:items-start md:justify-between border-b pb-6">
            <div class="">
                <div class="calendar-container bg-white mt-10 md:mt-0">
                    <table class="calendar table-auto text-center border-collapse">
                        <thead>
                            <tr>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Mån</th>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Tis</th>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Ons</th>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Tors</th>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Fre</th>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Lör</th>
                                <th class="px-2 py-1 text-sm font-semibold text-gray-700">Sön</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $currentYear = date('Y');
                            $currentMonth = date('m');

                            $firstDayOfMonth = strtotime("first day of {$currentYear}-{$currentMonth}");
                            $firstDayWeekday = date('w', $firstDayOfMonth);

                            if ($firstDayWeekday == 0) {
                                $firstDayWeekday = 7;
                            }

                            $daysInMonth = date('t', $firstDayOfMonth);

                            $finishedDates = [];
                            foreach ($finishedBooks as $book) {
                                $finishedDate = date('Y-m-d', strtotime($book['date']));
                                $finishedDates[] = $finishedDate;
                            }

                            $dayCounter = 1;
                            // Skapa kalendern
                            for ($i = 0; $i < 6; $i++) {
                                echo '<tr>';
                                for ($j = 0; $j < 7; $j++) {
                                    if ($i == 0 && $j < $firstDayWeekday - 1) {
                                        echo '<td class="px-2 py-1"></td>';
                                    } else if ($dayCounter <= $daysInMonth) {
                                        $currentDate = "{$currentYear}-{$currentMonth}-" . str_pad($dayCounter, 2, '0', STR_PAD_LEFT);
                                        $isBookFinished = in_array($currentDate, $finishedDates) ? 'book-finished' : '';
                                        $isToday = ($currentDate === date('Y-m-d')) ? 'bg-teal-100 text-teal-800 font-bold underline' : '';

                                        echo "<td class='px-2 py-1 text-sm text-gray-600  $isBookFinished $isToday'>{$dayCounter}</td>";
                                        $dayCounter++;
                                    } else {
                                        echo '<td class="px-2 py-1"></td>';
                                    }
                                }
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- Titel för månaden och året -->
                    <div class="text-center">
                        <?php
                        $currentYear = date('Y');
                        $currentMonth = date('m');

                        // Hämta dagens datum och månad
                        $monthName = date('F', strtotime("{$currentYear}-{$currentMonth}-01")); // Hämta månadsnamnet
                        $monthNameSwedish = [
                            'January' => 'Januari',
                            'February' => 'Februari',
                            'March' => 'Mars',
                            'April' => 'April',
                            'May' => 'Maj',
                            'June' => 'Juni',
                            'July' => 'Juli',
                            'August' => 'Augusti',
                            'September' => 'September',
                            'October' => 'Oktober',
                            'November' => 'November',
                            'December' => 'December',
                        ][$monthName];

                        echo "<h2 class='text-lg font-semibold text-gray-800'>{$monthNameSwedish} {$currentYear}</h2>";
                        ?>
                    </div>
                </div>


            </div>
            <!-- Visa redan tillagda böcker -->
            <div>
                <?php if ($booksResult->num_rows > 0): ?>
                    <div class="book-container flex flex-wrap gap-1 ">
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
                                class="book <?php echo $bgColor; ?> border-t-2 border-r-4 border-neutral-500 p-4 rounded-md flex flex-col items-center group relative overflow-hidden transition-all duration-300 w-[50px] hover:w-[200px] group-hover:w-[220px]">
                                <div
                                    class="book-title flex justify-center items-center mb-2 opacity-100 group-hover:opacity-0 transition-opacity duration-300">
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

                                    <!-- Redigera-knapp -->
                                    <button
                                        class="edit-btn bg-gray-500 hover:bg-gray-300 text-white text-xs afacad uppercase hover:text-gray-800 py-1 px-3 rounded-md mt-4"
                                        data-id="<?php echo $row['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                        data-author="<?php echo htmlspecialchars($row['author']); ?>"
                                        data-rating="<?php echo $row['rating']; ?>"
                                        data-date_finished="<?php echo $row['date_finished']; ?>">Redigera</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Du har inte lagt till några böcker ännu.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Modal för att redigera bok -->
    <div id="editBookModal"
        class="modal fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center hidden">
        <div class="modal-content bg-white p-6 rounded-md w-96">
            <h3 class="text-xl font-semibold mb-4">Redigera bok</h3>
            <form action="../actions/edit_read.php" method="POST">
                <input type="hidden" id="book_id" name="book_id">
                <div class="space-y-4">
                    <div class="flex flex-col space-y-2">
                        <label for="title" class="font-medium">Boktitel</label>
                        <input type="text" id="edit_title" name="title"
                            class="input w-full p-2 border border-gray-300 rounded-md" required>
                    </div>

                    <div class="flex flex-col space-y-2">
                        <label for="author" class="font-medium">Författare</label>
                        <input type="text" id="edit_author" name="author"
                            class="input w-full p-2 border border-gray-300 rounded-md" required>
                    </div>

                    <div class="flex flex-col space-y-2">
                        <label for="rating" class="font-medium">Betyg (1-5)</label>
                        <select id="edit_rating" name="rating"
                            class="input w-full p-2 border border-gray-300 rounded-md" required>
                            <option value="1">⭐</option>
                            <option value="2">⭐⭐</option>
                            <option value="3">⭐⭐⭐</option>
                            <option value="4">⭐⭐⭐⭐</option>
                            <option value="5">⭐⭐⭐⭐⭐</option>
                        </select>
                    </div>

                    <div class="flex flex-col space-y-2">
                        <label for="date_finished" class="font-medium">Datum när boken blev färdigläst</label>
                        <input type="date" id="edit_date_finished" name="date_finished"
                            class="input w-full p-2 border border-gray-300 rounded-md">
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="btn bg-teal-500 text-white py-2 px-4 rounded-md hover:bg-teal-600 transition">Uppdatera
                            bok</button>
                    </div>
                </div>
            </form>
            <button class="close-modal absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full">X</button>
        </div>
    </div>
</main>

<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const bookId = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const author = this.getAttribute('data-author');
            const rating = this.getAttribute('data-rating');
            const dateFinished = this.getAttribute('data-date_finished');

            document.getElementById('book_id').value = bookId;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_author').value = author;
            document.getElementById('edit_rating').value = rating;
            document.getElementById('edit_date_finished').value = dateFinished;

            document.getElementById('editBookModal').classList.remove('hidden');
        });
    });

    document.querySelector('.close-modal').addEventListener('click', function () {
        document.getElementById('editBookModal').classList.add('hidden');
    });

    window.addEventListener('click', function (event) {
        const modal = document.getElementById('editBookModal');
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });

    document.querySelectorAll('.calendar td.book-finished').forEach(cell => {
        cell.addEventListener('click', function () {
            const day = this.innerText;
            const bookDetails = getBookDetailsForDate(day);
            alert(bookDetails);
        });
    });

    function getBookDetailsForDate(day) {
        const book = finishedBooks.find(b => b.date === `${currentYear}-${currentMonth}-${day}`);
        return book ? `Bok: ${book.title}` : 'Ingen bok avslutad på detta datum';
    }

    // AJAX JS för att uppdatera currentPrivacy
    document.getElementById('reading-history-checkbox').addEventListener('change', function () {
        const isPublic = this.checked ? 1 : 0; // Om checkboxen är markerad, sätt till 1 (true), annars 0 (false)

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../actions/update_history_preference_action.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
            } else {
                console.error("Fel vid uppdatering.");
            }
        };
        xhr.send("user_id=<?php echo $user_id; ?>&is_public=" + isPublic);
    });
</script>


<?php include '../components/footer.php' ?>