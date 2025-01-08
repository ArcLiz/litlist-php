<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../controllers/AuthController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$display_name = $_SESSION['display_name'];
$avatar = $_SESSION['avatar'];
$bio = $_SESSION['bio'];

$authController = new AuthController($conn);
$isPublic = $authController->showReadingHistoryPrivacyForm($user_id);
?>

<div id="editProfileModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white p-6 rounded-lg shadow-md w-96">
        <h2 class="text-xl font-bold mb-4">Edit Profile</h2>
        <div class="border p-3 rounded-lg bg-neutral-100 afacad uppercase tracking-wide">
            <label for="reading-history-checkbox ">Offentlig läshistorik</label>
            <input type="checkbox" id="reading-history-checkbox" <?php echo $isPublic ? 'checked' : ''; ?>>
        </div>
        <form action="../actions/update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="display_name" class="block text-gray-700">Display Name:</label>
                <input type="text" name="display_name" id="display_name" class="border rounded w-full py-2 px-3"
                    value="<?php echo htmlspecialchars($display_name); ?>" required>
            </div>
            <div class="mb-4">
                <label for="avatar" class="block text-gray-700">Avatar:</label>
                <input type="file" name="avatar" id="avatar" class="border rounded w-full py-2 px-3">
            </div>
            <div class="mb-4">
                <label for="bio" class="block text-gray-700">Bio:</label>
                <textarea id="bio" name="bio" rows="4" class="border rounded w-full py-2 px-3"
                    required><?php echo htmlspecialchars($bio); ?></textarea>
            </div>
            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-md">Save</button>
            <button type="button" id="closeModal"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md">Close</button>
        </form>
    </div>
</div>

<script>
    const closeModal = document.getElementById('closeModal');

    closeModal.addEventListener('click', () => {
        document.getElementById('editProfileModal').classList.add('hidden');
    });

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