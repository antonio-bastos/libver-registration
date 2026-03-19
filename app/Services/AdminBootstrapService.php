<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;

class AdminBootstrapService
{
    private DatabaseManager $db;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function createAdmin(string $name, string $email, string $password): User
    {
        return $this->db->transaction(function () use ($name, $email, $password) {
            return User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMIN,
            ]);
        });
    }
}
