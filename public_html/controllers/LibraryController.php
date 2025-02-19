<?php
include_once __DIR__ . '/../models/Book.php';

class LibraryController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllLibraryBooksByUser($user_id, $offset = 0, $limit = 25, $searchTerm = '', $sortBy = 'title', $sortOrder = 'ASC')
    {
        $book = new Book($this->conn);
        $book->user_id = $user_id;

        $householdQuery = "SELECT household_id FROM auth_users WHERE id = ?";
        $householdStmt = $this->conn->prepare($householdQuery);
        $householdStmt->bind_param("i", $user_id);
        $householdStmt->execute();
        $householdResult = $householdStmt->get_result();
        $household_id = $householdResult->fetch_assoc()['household_id'] ?? null;

        $allowedSortColumns = ['title', 'author', 'genre', 'series', 'series_number', 'location'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'title';
        }

        $sortOrder = strtoupper($sortOrder);
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'ASC';
        }

        $query = "SELECT * FROM " . $book->table_name . " WHERE ";
        if ($household_id) {
            $query .= "household_id = ?";
        } else {
            $query .= "user_id = ?";
        }

        if ($searchTerm) {
            $query .= " AND (title LIKE ? OR author LIKE ? OR series LIKE ?)";
        }

        if ($searchTerm) {
            $query .= " ORDER BY author ASC, series ASC, series_number ASC, title ASC";
        } else {
            $query .= " ORDER BY $sortBy $sortOrder";
        }

        $query .= " LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);

        if ($searchTerm) {
            $searchTerm = "%$searchTerm%";
            if ($household_id) {
                $stmt->bind_param("isssii", $household_id, $searchTerm, $searchTerm, $searchTerm, $offset, $limit);
            } else {
                $stmt->bind_param("isssii", $user_id, $searchTerm, $searchTerm, $searchTerm, $offset, $limit);
            }
        } else {
            if ($household_id) {
                $stmt->bind_param("iii", $household_id, $offset, $limit);
            } else {
                $stmt->bind_param("iii", $user_id, $offset, $limit);
            }
        }

        $stmt->execute();
        return $stmt->get_result();
    }



    public function getBookDetails($book_id)
    {
        $book = new Book($this->conn);
        $book->id = $book_id;
        $book->read_one();
        return $book;
    }

    public function getTotalBooksByUser($user_id, $searchTerm = '')
    {
        $query = "SELECT COUNT(*) as total FROM library_books WHERE user_id = ?";

        if ($searchTerm) {
            $query .= " AND (title LIKE ? OR author LIKE ?)";
        }

        $stmt = $this->conn->prepare($query);
        if ($searchTerm) {
            $searchTerm = "%$searchTerm%";
            $stmt->bind_param("iss", $user_id, $searchTerm, $searchTerm);
        } else {
            $stmt->bind_param("i", $user_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getTopUsersByBooks($limit = 4)
    {
        $sql = "
    SELECT u.id AS user_id, u.username, u.display_name, u.avatar, COUNT(b.id) AS book_count
    FROM auth_users u
    LEFT JOIN library_books b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY book_count DESC
    LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();

        return $stmt->get_result();
    }


    public function getAllBooks()
    {
        $query = "SELECT * FROM library_books";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>