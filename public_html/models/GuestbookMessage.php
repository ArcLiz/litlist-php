<?php
class GuestbookMessage
{
    private $conn;
    private $table_name = "guestbook_messages";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $message;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addMessage($sender_id, $receiver_id, $message)
    {
        $query = "INSERT INTO " . $this->table_name . " (sender_id, receiver_id, message) VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function getMessagesForUser($receiver_id, $limit = 25, $offset = 0)
    {
        $query = "SELECT gm.*, u.username, u.avatar FROM " . $this->table_name . " gm 
              JOIN auth_users u ON gm.sender_id = u.id 
              WHERE gm.receiver_id = ? 
              ORDER BY gm.created_at DESC LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $receiver_id, $offset, $limit);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getMessagesBetweenUsers($sender_id, $receiver_id, $limit = 25, $offset = 0)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE sender_id = ? AND receiver_id = ? ORDER BY created_at DESC LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $sender_id, $receiver_id, $offset, $limit);

        $stmt->execute();
        return $stmt->get_result();
    }

    public function deleteMessage($message_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("i", $message_id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
