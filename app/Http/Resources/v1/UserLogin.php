<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLogin extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        // dd($this->tokens);
        return [
                'self' => [
                    'is_verified' => $this->mobile_verified_at ? true : false, // for mobile number registration
                    'is_admin' => $this->type == 'admin' ? true : false, // for admin panel
                    'is_supplier' => $this->type == 'supplier' ? true : false, // for seller panel
                    'is_customer' => $this->type == 'customer' ? true : false, // for customer panel
                    'is_complate_profile' => true, // for complete profile
                    'mobile_verified_at' => $this->mobile_verified_at,
                    'email_verified_at' => $this->email_verified_at,
                ],
                'token' => [
                    // mobile_verified_at if not null then mobile number is verified
                    'is_active' => false,
                    'created_at' => null,
                    'expires_at' => null,
                ]
        ];

    }
}
