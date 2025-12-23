<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'birth_date' => $this->birth_date,
            'phone' => $this->phone,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}