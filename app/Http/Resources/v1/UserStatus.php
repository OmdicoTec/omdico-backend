<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Controllers\StatusInfoController;

class UserStatus extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // get user status steps from StatusInfoController
        // show user complete profile or not
        $statusInfoController = new StatusInfoController();
        $user_status = $statusInfoController->filledStatus($request);

        // return parent::toArray($request);
        // dd($this->tokens);
        return [
            'user_status' => [
                'self' => [
                    'is_verified' => $this->mobile_verified_at ? true : false, // for mobile number registration
                    'is_admin' => $this->type == 'admin' ? true : false, // for admin panel
                    'is_supplier' => $this->type == 'supplier' ? true : false, // for seller panel
                    'is_customer' => $this->type == 'customer' ? true : false, // for customer panel
                    'is_complate_profile' => true, // for complete profile
                    'mobile_verified_at' => $this->mobile_verified_at,
                    'email_verified_at' => $this->email_verified_at,
                    'profile_steps' => $user_status
                ],
                'token' => [
                    // mobile_verified_at if not null then mobile number is verified
                    'is_active' => $this->token()->mobile_verified_at ? true : false, // for mobile OTP verification
                    // 'is_valid' => $this->token()->expires_at > now() ? true : false, // for mobile OTP verification
                    'created_at' => $this->token()->created_at,
                    'expires_at' => $this->token()->expires_at,
                ]
            ],
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
