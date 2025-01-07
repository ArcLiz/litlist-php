<?php

class ReadController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAllReadBooksByUser($user_id, $offset = 0, $limit = 25, $searchTerm = '', $sortBy = 'date_finished', $sortOrder = 'DESC')
    {
        $query = "SELECT * FROM library_read WHERE user_id = ?";

        if ($searchTerm) {
            $query .= " AND (title LIKE ? OR author LIKE ?)";
        }

        $query .= " ORDER BY $sortBy $sortOrder LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);

        if ($searchTerm) {
            $searchTerm = "%$searchTerm%";
            $stmt->bind_param("issii", $user_id, $searchTerm, $searchTerm, $offset, $limit);
        } else {
            $stmt->bind_param("iii", $user_id, $offset, $limit);
        }

        $stmt->execute();
        return $stmt->get_result();
    }



    public function getTotalReadBooksByUser($user_id, $searchTerm = '')
    {
        $sql = "SELECT COUNT(*) AS total FROM library_read WHERE user_id = ? AND (title LIKE ? OR author LIKE ?)";
        $stmt = $this->conn->prepare($sql);
        $searchTerm = '%' . $searchTerm . '%';  // Gör om sökordet till ett LIKE-mönster
        $stmt->bind_param("iss", $user_id, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getBooksReadThisYear($user_id)
    {
        $query = "SELECT COUNT(*) AS books_in_year
                  FROM library_read
                  WHERE user_id = ? AND YEAR(date_finished) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['books_in_year'];
    }

    public function getBooksReadThisMonth($user_id)
    {
        $query = "SELECT COUNT(*) AS books_in_month
                  FROM library_read
                  WHERE user_id = ? AND MONTH(date_finished) = MONTH(CURDATE()) AND YEAR(date_finished) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['books_in_month'];
    }

    public function getFavoriteAuthor($user_id)
    {
        $query = "SELECT author, COUNT(*) AS author_count
                  FROM library_read
                  WHERE user_id = ?
                  GROUP BY author
                  ORDER BY author_count DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAverageRating($user_id)
    {
        $query = "SELECT AVG(rating) AS average_rating
                  FROM library_read
                  WHERE user_id = ? AND rating IS NOT NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['average_rating'];
    }
}
