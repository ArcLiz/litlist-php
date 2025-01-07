<?php
class Wishlist
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
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

    // Lägg till en bok i önskelistan
    public function addBook($user_id, $title, $author)
    {
        $sql = "INSERT INTO wishlist_books (user_id, title, author) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $title, $author);
        return $stmt->execute();
    }

    // Uppdatera en bok i önskelistan
    public function updateBook($user_id, $book_id, $title, $author)
    {
        $sql = "UPDATE wishlist_books SET title = ?, author = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $title, $author, $book_id, $user_id);
        return $stmt->execute();
    }

    // Ta bort en bok från önskelistan
    public function deleteBook($user_id, $book_id)
    {
        $sql = "DELETE FROM wishlist_books WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $book_id, $user_id);
        return $stmt->execute();
    }
}
