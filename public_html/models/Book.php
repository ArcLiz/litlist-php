<?php
class Book
{
    private $conn;
    public $table_name = "library_books";

    public $id;
    public $title;
    public $author;
    public $genre;
    public $user_id; // FK
    public $published_year;
    public $description;
    public $comment;
    public $location;
    public $created_at;
    public $series;
    public $series_number;
    public $cover_image;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create a new book
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
              (title, author, genre, user_id, published_year, created_at, description, comment, location, series, series_number, cover_image) 
              VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->genre = htmlspecialchars(strip_tags($this->genre));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->published_year = htmlspecialchars(strip_tags($this->published_year));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->series = htmlspecialchars(strip_tags($this->series));
        $this->series_number = htmlspecialchars(strip_tags($this->series_number));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));

        // Bind parameters using `bind_param` for `mysqli`
        $stmt->bind_param(
            "sssisssssss",
            $this->title,
            $this->author,
            $this->genre,
            $this->user_id,
            $this->published_year,
            $this->description,
            $this->comment,
            $this->location,
            $this->series,
            $this->series_number,
            $this->cover_image
        );

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all books by user
    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Read a single book by id
    public function read_one()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->genre = $row['genre'];
            $this->user_id = $row['user_id'];
            $this->published_year = $row['published_year'];
            $this->description = $row['description'];
            $this->comment = $row['comment'];
            $this->location = $row['location'];
            $this->series = $row['series'];
            $this->series_number = $row['series_number'];
            $this->cover_image = $row['cover_image'];
        }
    }

    public function getBookByTitleAndUser()
    {
        $query = "SELECT id, author FROM " . $this->table_name . " WHERE title = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $this->title, $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Return the book's info (including author) if it exists
    }

    public function checkBookExists($title, $user_id)
    {
        // SQL-frågan uppdateras för att inkludera både titeln och användarens ID
        $stmt = $this->conn->prepare("SELECT title, author FROM library_books WHERE title = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("si", $title, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Returnerar bokens titel och författare om den finns
        } else {
            return null; // Ingen bok hittades
        }
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
              SET title = ?, author = ?, genre = ?, published_year = ?, description = ?, comment = ?, location = ?, series = ?, series_number = ?, cover_image = ? 
              WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->genre = htmlspecialchars(strip_tags($this->genre));
        $this->published_year = htmlspecialchars(strip_tags($this->published_year));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->series = htmlspecialchars(strip_tags($this->series));
        $this->series_number = htmlspecialchars(strip_tags($this->series_number));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));

        $stmt->bind_param(
            "sssisissssi",
            $this->title,
            $this->author,
            $this->genre,
            $this->published_year,
            $this->description,
            $this->comment,
            $this->location,
            $this->series,
            $this->series_number,
            $this->cover_image,
            $this->id
        );

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        return $stmt->execute();
    }
}
?>