<?php

namespace App\Models;

use Laravel\Passport\Token as PassportToken;
// for access $notifiable
use Illuminate\Notifications\Notifiable;

// for login user and when user create new access token, send sms OTP to user
use App\Traits\MustVerifyMobile;
use App\Interface\MustVerifyMobile as IMustVerifyMobile;

class AccessToken extends PassportToken  implements IMustVerifyMobile
{
    // access token model to use sms
    use MustVerifyMobile, Notifiable;
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'bool',
        'expires_at' => 'datetime',
        'number_verified_at' => 'datetime',
        'mobile_verify_code_sent_at' => 'datetime',
        'mobile_last_attempt_date' => 'datetime',
    ];

    // get user mobile number from user model
    public function userMobileNumber()
    {
        return $this->user->mobile_number;
    }
}
