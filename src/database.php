<?php

namespace App;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (static::$pdo === null) {
            // The database file is located in the project root.
            $dbPath = __DIR__ . '/../database.sqlite';
            static::$pdo = new PDO('sqlite:' . $dbPath);
            static::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            static::migrate();
        }

        return static::$pdo;
    }

    private static function migrate()
    {
        // Create tables if they don't exist
        $queries = [
            // Users table
            // only an id and a username for simplicity
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL
            )",

            // Groups table
            // only id, name, and creation date
            // since the question did not have more clarification about the group
            // if I'm to suggest more info it would be group description, joining conditions, and maybe a logo
            "CREATE TABLE IF NOT EXISTS groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",

            // Pivot table for group memberships
            // it is not clear in the question if the user will leave a group and re-join 
            // so I only assumed a joining date not a leaving date
            "CREATE TABLE IF NOT EXISTS group_user (
                group_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (group_id, user_id),
                FOREIGN KEY (group_id) REFERENCES groups(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )",

            // Messages table
            "CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                group_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (group_id) REFERENCES groups(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )",
        ];

        foreach ($queries as $query) {
            static::$pdo->exec($query);
        }
    }
}
