<?php
include_once '../../inc/dbmysqli.php';

class User
{
    private $conn;
    private $table_name = "auth_users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $user_category;
    public $created_at;
    public $bio;
    public $display_name;
    public $avatar;
    public $household_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create a new user
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (username, password, email, user_category, display_name, bio, avatar, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_BCRYPT); // Hash the password
        $this->username = htmlspecialchars(strip_tags($this->username ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->display_name = htmlspecialchars(strip_tags($this->display_name ?? ''));
        $this->bio = htmlspecialchars(strip_tags($this->bio ?? ''));
        $this->avatar = htmlspecialchars(strip_tags($this->avatar ?? ''));
        $this->user_category = htmlspecialchars(strip_tags($this->user_category));


        $stmt->bind_param("sssssss", $this->username, $this->password, $this->email, $this->user_category, $this->display_name, $this->bio, $this->avatar);

        return $stmt->execute();
    }

    // Find existing user
    public function login()
    {
        $query = "SELECT id, username, password, email, display_name, bio, avatar, user_category, created_at, household_id 
              FROM " . $this->table_name . " 
              WHERE username = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->display_name = $row['display_name'];
                $this->bio = $row['bio'];
                $this->avatar = $row['avatar'];
                $this->user_category = $row['user_category'];
                $this->created_at = $row['created_at'];
                $this->household_id = $row['household_id']; // Lägg till household_id här

                echo "User Data: ";
                print_r($row);
                return true;
            }
        }
        return false;
    }


    // Fetch user by ID
    public function getUserById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function updateProfile()
    {
        $query = "UPDATE " . $this->table_name . " SET display_name = ?, avatar = ?, email = ?, bio = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->display_name = htmlspecialchars(strip_tags($this->display_name));
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->bio = htmlspecialchars($this->bio);

        $stmt->bind_param("ssssi", $this->display_name, $this->avatar, $this->email, $this->bio, $this->id);

        return $stmt->execute();
    }

}
?>