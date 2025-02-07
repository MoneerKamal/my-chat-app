<?php

namespace App\Models;
use App\Database;
use PDO;

class User
{
    public ?int $id;
    public string $username;

    public function __construct(?int $id, string $username)
    {
        $this->id = $id;
        $this->username = $username;
        
    }
    public static function getUserOrCreate($username){
        $pdo = Database::getConnection();

        // Try to fetch an existing user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self((int)$row['id'], $row['username']);
        }
        else{
            $stmt = $pdo->prepare("INSERT INTO users (username) VALUES (:username)");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $newId = (int)$pdo->lastInsertId();

        return new self($newId, $username);
        }
    }
   
}
