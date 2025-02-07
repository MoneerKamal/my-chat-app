<?php

namespace App\Models;

use PDO;
use App\Database;

class Message
{
    public ?int $id;
    public int $group_id;
    public int $user_id;
    public string $content;
    public string $created_at;

    public function __construct() {}

    public function save()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
        INSERT INTO messages (group_id, user_id, content)
        VALUES (:group_id, :user_id, :content)
    ");
        $stmt->bindValue(':group_id', $this->group_id);
        $stmt->bindValue(':user_id', $this->user_id);
        $stmt->bindValue(':content', $this->content);
        $stmt->execute();
        $messageId = (int)$pdo->lastInsertId();

        // Fetch the newly created message
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = :id");
        $stmt->bindValue(':id', $messageId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {

            $this->id = $messageId;
            $this->created_at = $row['created_at'];
            return true;
        } else {
            return false;
        }
    }
    public static function getMessages($group_id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
         SELECT m.*, u.username
         FROM messages m
         JOIN users u ON m.user_id = u.id
         WHERE m.group_id = :group_id
         ORDER BY m.created_at ASC
     ");
        $stmt->bindValue(':group_id', $group_id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($rows as $row) {
            $message = new self();
            $message->id = $row['id'];
            $message->group_id   = $row['group_id'];
            $message->user_id    = $row['user_id'];

            $message->content    = $row['content'];
            $message->created_at = $row['created_at'];
            $messages[] = $message;
        }
        return $messages;
    }
}
