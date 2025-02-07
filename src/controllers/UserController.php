<?php

namespace App\Controllers;

use App\Database;
use App\Models\User;
use PDO;

class UserController
{
    public static function getOrCreateUser(string $username): User
    {
       
        return User::getUserOrCreate($username);
    }
}
