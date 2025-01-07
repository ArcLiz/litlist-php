<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
} else {
    $username = null;
}

include '../components/header.php';
?>
<div class="flex justify-center mt-12">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Skapa ett konto</h1>

        <form action="../actions/register_action.php" method="POST" class="space-y-6">
            <!-- Användarnamn -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Användarnamn</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <!-- E-post -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-post</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <!-- Lösenord -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Lösenord</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <!-- Bekräfta lösenord -->
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Bekräfta lösenord</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <!-- Registrera knapp -->
            <div>
                <button type="submit"
                    class="w-full bg-teal-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    Registrera
                </button>
            </div>
        </form>

        <!-- Logga in länk -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Har du redan ett konto?
            <a href="auth_signin.php" class="text-teal-600 hover:text-teal-500 font-medium">
                Logga in här
            </a>
        </p>
    </div>
</div>

<?php include '../components/footer.php' ?>