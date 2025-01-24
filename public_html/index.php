<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('../inc/dbmysqli.php');
include_once 'controllers/LibraryController.php';

$libraryController = new LibraryController($conn);
$topUsers = $libraryController->getTopUsersByBooks(4);

include 'components/header.php';

?>

<!-- Container -->
<main class="grow w-screen bg-gradient-to-b from-neutral-900 to-neutral-700 flex items-center">
    <!-- Header -->
    <div class="mx-auto container">
        <div id="swap-container"
            class="flex flex-col-reverse md:flex-row bg-white justify-between mx-auto max-w-4xl md:h-[550px] md:mt-28 rounded-2xl shadow-lg shadow-neutral-900">
            <!-- Vänster sektion STANDARD -->
            <div id="left-section"
                class="box md:rounded-l-2xl bg-teal-500 lg:p-20 p-12 md:w-5/12 flex flex-col justify-center items-center space-y-5 text-white">
                <h1 class="afacad font-bold text-3xl">Ny på litlist?</h1>
                <p class="text-center">Skapa ett konto snabbt och enkelt för att kunna bygga ditt eget bibliotek på
                    nätet och dela böcker med andra!</p>
                <div>
                    <button id="register-btn"
                        class="active:scale-90 border border-white text-white py-3 px-6 uppercase rounded-3xl font-medium hover:bg-teal-700 ">
                        Registrera
                    </button>
                </div>
            </div>

            <!-- Höger sektion STANDARD -->
            <div id="right-section"
                class="md:rounded-r-2xl bg-white lg:p-20 p-12 md:w-7/12 space-y-5 flex flex-col justify-center ">
                <h1 class="afacad font-bold text-3xl text-teal-500">Välkommen tillbaka!</h1>

                <form action="../actions/signin_action.php" method="POST" class="space-y-6">
                    <!-- Användarnamn -->
                    <div>
                        <label for="username" class="hidden text-sm font-medium text-gray-700">Användarnamn</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-gray-400"></i>
                            </span>
                            <input type="text" id="username" name="username" placeholder="Användarnamn" required
                                class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Lösenord -->
                    <div>
                        <label for="password" class="hidden text-sm font-medium text-gray-700">Lösenord</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Lösenord" required
                                class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Logga in knapp -->
                    <div class="flex justify-end">
                        <button type="submit"
                            class="active:scale-90 border border-teal-600 bg-teal-600 text-white hover:bg-white hover:text-teal-600 py-3 px-6 uppercase rounded-3xl font-medium ">
                            Logga in
                        </button>
                    </div>
                </form>
            </div>

            <!-- Vänster sektion NON-USER -->
            <div id="left-section-nonuser"
                class="md:rounded-l-2xl bg-white lg:p-20 p-12 w-screen md:w-7/12 space-y-5 hidden flex flex-col justify-center">
                <h1 class="afacad font-bold text-3xl text-teal-500 mb-6 ">Registrera ett konto</h1>
                <form action="../actions/register_action.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="hidden text-sm font-medium text-gray-700">Användarnamn</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-gray-400"></i>
                            </span>
                            <input type="text" id="username" name="username" placeholder="Användarnamn" required
                                class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="hidden text-sm font-medium text-gray-700">E-post</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-regular fa-envelope text-gray-400"></i>
                            </span>
                            <input type="email" id="email" name="email" placeholder="E-post" required
                                class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="hidden text-sm font-medium text-gray-700">Lösenord</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Lösenord" required
                                class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="confirm_password" class="hidden text-sm font-medium text-gray-700">Bekräfta
                            lösenord</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </span>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Bekräfta lösenord" required
                                class="block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="active:scale-90 border border-teal-600 bg-teal-600 text-white hover:bg-white hover:text-teal-600 py-3 px-6 uppercase rounded-3xl font-medium mt-6">
                            Registrera
                        </button>
                    </div>
                </form>
            </div>

            <!-- Höger sektion NON-USER -->
            <div id="right-section-nonuser"
                class="md:rounded-r-2xl bg-teal-500 lg:p-20 p-12 w-screen md:w-5/12 flex flex-col justify-center items-center space-y-5 text-white hidden">
                <h1 class="afacad font-bold text-3xl">Har du redan ett konto?</h1>
                <p class="text-center">Klicka nedan för att logga in!</p>
                <div>
                    <button id="login-btn"
                        class="active:scale-90 border border-white text-white py-3 px-6 uppercase rounded-3xl font-medium hover:bg-teal-700 ">
                        Logga in
                    </button>
                </div>
            </div>

        </div>

        <!-- Quick Links -->
        <div
            class="flex flex-col justify-center text-white mx-auto mt-10 p-6 md:p-10 md:mx-10">
            <div>
                <h1 class="font-semibold text-3xl md:text-center mb-4 text-teal-400 afacad">Inspiration <i
                        class="fa-solid fa-arrow-down text-sm"></i></h1>
                <h2 class="text-xl md:text-center afacad">Nedan hittar du våra populäraste användare.</h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 md:mx-auto gap-6 md:gap-10 mt-10">
                <?php while ($row = $topUsers->fetch_assoc()): ?>
                    <a href="/views/library_guestview.php?user_id=<?= $row['user_id'] ?>&sort_by=title&sort_order=ASC"
                        class="block">
                        <div
                            class="border bg-white/5 border-teal-700 rounded-t-full p-4 hover:scale-105 hover:shadow hover:shadow-neutral-800">
                            <img src="<?= $row['avatar'] ? '../uploads/avatars/' . htmlspecialchars($row['avatar']) : '/assets/noprofile.jpg' ?>"
                                alt="Avatar" class="rounded-full h-[100px] w-[100px] mx-auto">
                            <h1 class="text-center text-xl text-semibold afacad text-teal-400">
                                <?= htmlspecialchars($row['display_name'] ?: $row['username']) ?>
                            </h1>
                            <p class="text-sm text-center text-neutral-400 -mt-2">
                                @<?= htmlspecialchars(strtolower($row['username'])) ?>
                            </p>
                            <p class="text-center text-teal-500 block mt-2">
                                <?= htmlspecialchars($row['book_count']) ?> böcker
                            </p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

        </div>
    </div>

</main>

<?php include 'components/footer.php'; ?>

<script>
    const registerBtn = document.getElementById('register-btn');
    const loginBtn = document.getElementById('login-btn');

    const leftSection = document.getElementById('left-section');
    const rightSection = document.getElementById('right-section');

    const leftSectionNonUser = document.getElementById('left-section-nonuser');
    const rightSectionNonUser = document.getElementById('right-section-nonuser');

    registerBtn.addEventListener('click', () => {
        leftSection.classList.add('md:transition', 'md:duration-700', 'md:translate-x-[520px]', 'z-40');
        rightSection.classList.add('md:transition', 'md:duration-700', 'md:-translate-x-[350px]');

        setTimeout(() => {
            leftSection.classList.add('hidden');
            rightSection.classList.add('hidden');
            leftSection.classList.remove('md:transition', 'md:duration-700', 'md:translate-x-[520px]');
            rightSection.classList.remove('md:transition', 'md:duration-700', 'md:-translate-x-[350px]');

            leftSectionNonUser.classList.remove('hidden');
            rightSectionNonUser.classList.remove('hidden');
        }, 700);
    });

    loginBtn.addEventListener('click', () => {
        leftSectionNonUser.classList.add('md:transition', 'md:duration-700', 'md:translate-x-[350px]');
        rightSectionNonUser.classList.add('md:transition', 'md:duration-700', 'md:-translate-x-[520px]');

        setTimeout(() => {
            leftSectionNonUser.classList.add('hidden');
            rightSectionNonUser.classList.add('hidden');
            leftSectionNonUser.classList.remove('md:transition', 'md:duration-700', 'md:translate-x-[350px]');
            rightSectionNonUser.classList.remove('md:transition', 'md:duration-700', 'md:-translate-x-[520px]');

            leftSection.classList.remove('hidden');
            rightSection.classList.remove('hidden');
        }, 700);
    });
</script>