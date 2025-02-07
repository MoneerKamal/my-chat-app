<?php

namespace App\Models;

use PDO;
use App\Database;

class Group
{
    public ?int $id;
    public string $name;
    public ?string $created_at;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function save()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO groups (name) VALUES (:name)");
        $stmt->bindValue(':name', $this->name);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            return false;
        }
        $this->id = (int)$pdo->lastInsertId();
        // Fetch the newly created group
        $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = :id");
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->created_at = $row['created_at'];
        return true;
    }

    public static function getGroupByName(string $groupName)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM groups WHERE name = :name");
        $stmt->bindValue(':name', $groupName);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $group = new self($groupName);
            $group->id = $row['id'];
            $group->created_at = $row['created_at'];
            return $group;
        } else {
            return null;
        }
    }
    public static function getGroupById(int $id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $group = new self($row['name']);
            $group->id = $row['id'];
            $group->created_at = $row['created_at'];
            return $group;
        } else {
            return null;
        }
    }
    public function inGroup($user_id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM group_user WHERE group_id = :group_id AND user_id = :user_id");
        $stmt->bindValue(':group_id', $this->id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        if ($stmt->fetch()) {
            // Already joined
            return true;
        } else {
            return false;
        }
    }
    public function joinGroup($user_id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO group_user (group_id, user_id) VALUES (:group_id, :user_id)");
        $stmt->bindValue(':group_id', $this->id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            return false;
        } else {
            return true;
        }
    }
}
