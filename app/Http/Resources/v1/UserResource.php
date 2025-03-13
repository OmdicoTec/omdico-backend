<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * php artisan make:resource v1/UserResource
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'user_info' => [
                'id' => $this->id,
                'name' => $this->name,
                'family' => $this->family,
                'type' => $this->type,
                'email' => $this->email,
                'mobile_number' => $this->mobile_number,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
