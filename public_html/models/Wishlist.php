<?php
class Wishlist
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;

        // Se till att teckenkodningen är korrekt för varje förfrågan
        $this->conn->set_charset("utf8mb4");
    }

    // Hämta önskelistan för en användare
    public function getWishlist($user_id)
    {
        $sql = "SELECT id, title, author FROM wishlist_books WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $wishlist = [];
        while ($row = $result->fetch_assoc()) {
            $wishlist[] = $row;
        }

        return $wishlist;
    }
}