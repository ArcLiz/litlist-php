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
        <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Logga in</h1>

        <form action="../actions/signin_action.php" method="POST" class="space-y-6">
            <!-- Användarnamn -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Användarnamn</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <!-- Lösenord -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Lösenord</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
            </div>

            <!-- Logga in knapp -->
            <div>
                <button type="submit"
                    class="w-full bg-teal-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    Logga in
                </button>
            </div>
        </form>

        <!-- Registrering länk -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Har du inget konto?
            <a href="auth_signup.php" class="text-teal-600 hover:text-teal-500 font-medium">
                Registrera dig här
            </a>
        </p>
    </div>
</div>

<?php include '../components/footer.php' ?>