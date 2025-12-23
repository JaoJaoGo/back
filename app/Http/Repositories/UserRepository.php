<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function count(): int
    {
        return User::count();
    }
}