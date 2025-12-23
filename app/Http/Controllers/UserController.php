<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Responses\UserResponse;
use App\Http\Services\UserService;

class UserController extends Controller
{
    public function store(StoreUserRequest $request, UserService $service)
    {
        $user = $service->create($request->validated());

        return UserResponse::created($user);
    }
}