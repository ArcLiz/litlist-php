<?php

if (!isset($_SESSION['user_id']) && isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];

    // Kontrollera token i databasen
    $stmt = $conn->prepare("SELECT id, username, email, display_name, bio, avatar, created_at, user_category, household_id FROM users WHERE auth_token = ? AND token_expires > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Logga in användaren
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['bio'] = $user['bio'];
        $_SESSION['avatar'] = $user['avatar'];
        $_SESSION['created_at'] = $user['created_at'];
        $_SESSION['user_category'] = $user['user_category'];
        $_SESSION['household_id'] = $user['household_id'];
    } else {
        // Ogiltig token, rensa cookie
        setcookie('auth_token', '', time() - 3600, '/');
    }
}
