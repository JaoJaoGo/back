<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function create(array $data)
    {
        if ($this->repository->count() >= 2) {
            throw new RuntimeException("Número máximo de usuários atingidos.");
        }

        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }
}