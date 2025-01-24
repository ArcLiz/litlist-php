<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LitList</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">

    <script src='https://cdn.tailwindcss.com'></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"
        integrity="sha512-JyCZjCOZoyeQZSd5+YEAcFgz2fowJ1F1hyJOXgtKu4llIa0KneLcidn5bwfutiehUTiOuK87A986BZJMko0eWQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"
        integrity="sha512-UtLOu9C7NuThQhuXXrGwx9Jb/z9zPQJctuAgNUBK3Z6kkSYT9wJ+2+dh6klS+TDBCV9kNPBbAxbVD+vCcfGPaA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <link rel="stylesheet" href="../assets/style.css">

</head>

<body class="flex flex-col min-h-screen overflow-x-hidden">
    <!-- Navigation -->
    <header class="bg-neutral-900 text-white">
        <nav class="lg:container mx-auto p-2 flex items-center">
            <!-- LAPTOP / DESKTOP -->
            <div class="flex w-screen justify-between items-center">
                <!-- Logo -->
                <a href="<?= isset($_SESSION['user_id']) ? '../views/home.php' : '../index.php' ?>"
                    class="z-40 flex divide-x divide-neutral-700 space-x-3">
                    <img src="../assets/s-logo.svg" alt="" class="h-[30px] ml-3 my-auto">
                    <h1 class="pl-3 text-4xl tracking-wider afacad hidden sm:block">litlist</h1>
                </a>


                <div class="flex space-x-5">
                    <!-- ADD NEW BOOK -->
                    <form id="newBookForm" method="POST"
                        class="flex items-center relative w-58 my-2 text-black <?= !isset($_SESSION['user_id']) ? 'hidden' : '' ?>">
                        <input type="text" id="new_book" placeholder="Ange ny boktitel"
                            class="border border-gray-300 rounded-full py-1 px-4 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent">

                        <input type="hidden" name="user_id" value="<?= $user_id ?>">

                        <button id="addBookButton" class="absolute right-3 text-gray-500 hover:text-green-400">
                            <i class="fa-solid fa-file-circle-plus h-5 w-5"></i>
                        </button>
                    </form>


                    <!-- Links (conditionally shown based on session) -->
                    <div class="hidden lg:flex items-center space-x-5 uppercase afacad tracking-wider">
                        <?php if (isset($_SESSION['user_id'])): ?>

                            <?php $username = $_SESSION['username']; ?>
                            <a href="/views/home.php" class="hover:text-teal-400 hover:underline">
                                Hem
                            </a>
                            <a href="/views/user_list.php" class="hover:text-teal-400 hover:underline">
                                Vänner
                            </a>
                            <a href="/views/user_profile.php" class="hover:text-teal-400 hover:underline">
                                <?php echo htmlspecialchars($username); ?>
                            </a>
                            <a href="/views/library_table.php" class="hover:text-teal-400 hover:underline">Bibliotek</a>
                            <a href="/views/user_read.php" class="hover:text-teal-400 hover:underline">Nyläst</a>
                            <a href="/actions/signout_action.php"
                                class="px-2 py-1 rounded-lg bg-rose-700 hover:bg-rose-900">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        <?php else: ?>
                            <!-- Kontrollera om URL:en innehåller 'library_guestview' -->
                            <?php if (strpos($_SERVER['REQUEST_URI'], 'library_guestview') !== false): ?>
                                <div class="flex text-teal-400">
                                    <a href="../index.php" class="font-semibold hover:text-teal-200 hover:underline">Logga in /
                                        Skapa konto</a>
                                    <p class="lowercase">&nbsp för att göra ditt eget bibliotek.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Hamburger Button -->
                    <div class="lg:hidden ml-auto my-auto px-3">
                        <button id="menu-btn" type="button" class="z-40 block hamburger lg:hidden focus:outline-none">
                            <span class="hamburger-top"></span>
                            <span class="hamburger-middle"></span>
                            <span class="hamburger-bottom"></span>
                        </button>
                    </div>
                </div>
            </div>


            <!-- MOBILE / TABLET Menu -->
            <div id="menu"
                class="z-20 absolute top-0 bottom-0 left-0 hidden flex-col self-end w-full min-h-screen pb-1 pt-32 px-12 space-y-3 text-2xl text-white uppercase bg-black">

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/views/home.php" class="hover:text-teal-400">Hem</a>
                    <a href="/views/user_list.php" class="hover:text-teal-400">Vänner</a>
                    <a href="/views/user_profile.php"
                        class="hover:text-teal-400 border-t border-neutral-800 pt-2 "><?php echo htmlspecialchars($username); ?></a>
                    <a href="/views/library_table.php" class="hover:text-teal-400">Bibliotek</a>
                    <a href="/views/user_read.php" class="hover:text-teal-400">Nyläst</a>
                    <a href="/actions/signout_action.php"
                        class="hover:text-teal-400 border-t border-neutral-800 pt-2 text-rose-300">Logga
                        ut</a>
                <?php else: ?>
                    <a href="../index.php" class="hover:text-teal-400">Skapa konto</a>
                    <a href="../index.php" class="hover:text-teal-400">Logga in</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Hamburgermenyyyn -->
    <script>
        const btn = document.getElementById("menu-btn");
        const menu = document.getElementById("menu");

        btn.addEventListener("click", navToggle);

        function navToggle() {
            btn.classList.toggle("open");
            menu.classList.toggle("flex");
            menu.classList.toggle("hidden");
        }
    </script>

    <!-- ajax popup control for existing book -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#addBookButton').click(function (event) {
                event.preventDefault();
                const userId = $('#user_id').val();
                const newBookTitle = $('#new_book').val();
                if (!newBookTitle) {
                    alert('Vänligen ange en titel för boken.');
                    return;
                }

                // Check if title already exists
                $.ajax({
                    url: '../actions/check_book_exists.php',
                    type: 'POST',
                    data: {
                        title: newBookTitle,
                        user_id: userId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.exists) {
                            $('#modalMessage').html(`<p class="text-sm">Du har redan en bok med titeln </p>"${response.title}"<br>av "${response.author}"<br><br>Vill du lägga till titeln ändå?`);
                            $('#confirmModal').removeClass('hidden'); // Show modal

                            $('#confirmYes').off('click').on('click', function () {
                                window.location.href = `library_create.php?title=${encodeURIComponent(newBookTitle)}`;
                            });

                            $('#confirmNo').off('click').on('click', function () {
                                $('#confirmModal').addClass('hidden'); // Hide modal
                                $('#new_book').val('');
                            });
                        } else {
                            window.location.href = `library_create.php?title=${encodeURIComponent(newBookTitle)}`;
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('Ett fel inträffade när boken skulle kontrolleras.');
                    }
                });
            });
        });
    </script>

    <!-- Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-11/12 lg:w-1/3">
            <div
                class="bg-gray-100 uppercase text-center tracking-wider text-gray-500 rounded-t-lg p-2 hidden lg:block">
                <p>Möjlig dublett</p>
            </div>
            <div class="p-4">
                <p id="modalMessage" class="text-center"></p>
            </div>
            <div class="flex justify-center space-x-6 pb-4">
                <button id="confirmYes" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Ja</button>
                <button id="confirmNo" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Nej</button>
            </div>
        </div>
    </div>