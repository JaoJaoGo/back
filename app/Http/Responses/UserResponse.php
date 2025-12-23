<?php

namespace App\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UserResponse
{
    public static function created($user): JsonResponse
    {
        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'data' => new UserResource($user),
        ], 201);
    }
}