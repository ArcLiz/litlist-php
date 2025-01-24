<?php
include_once '../../inc/dbmysqli.php';
include_once '../models/User.php';

class AuthController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register($username, $password, $email, $user_category)
    {
        $user = new User($this->conn);
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->user_category = $user_category;

        return $user->create();
    }

    public function login($username, $password)
    {
        $user = new User($this->conn);
        $user->username = $username;
        $user->password = $password;

        if ($user->login()) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['email'] = $user->email;
            $_SESSION['display_name'] = $user->display_name;
            $_SESSION['bio'] = $user->bio;
            $_SESSION['avatar'] = $user->avatar;
            $_SESSION['created_at'] = $user->created_at;
            $_SESSION['user_category'] = $user->user_category;
            $_SESSION['household_id'] = $user->household_id;
            return true;
        }
        return false;
    }

    public function getUsernameById($user_id)
    {
        $stmt = $this->conn->prepare("SELECT username FROM auth_users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['username'];
        } else {
            return null; // Om användaren inte hittas
        }
    }

    public function getBioById($user_id)
    {
        $stmt = $this->conn->prepare("SELECT bio FROM auth_users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['bio'];
        } else {
            return null; // Om användaren inte hittas
        }
    }

    public function getAvatarById($user_id)
    {
        $stmt = $this->conn->prepare("SELECT avatar FROM auth_users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['avatar'];
        } else {
            return null; // Om användaren inte hittas
        }
    }

    public function updateReadingHistoryPrivacy($user_id, $is_public)
    {
        $stmt = $this->conn->prepare("UPDATE auth_users SET public = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_public, $user_id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function showReadingHistoryPrivacyForm($user_id)
    {
        $stmt = $this->conn->prepare("SELECT public FROM auth_users WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['public'];
        } else {
            return null;
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>