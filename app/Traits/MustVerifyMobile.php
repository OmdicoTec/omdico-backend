<?php

namespace App\Traits;

use App\Notifications\SendVerifySMS;

trait MustVerifyMobile
{
    /**
     * Has the user verified their mobile number?
     *
     * @return bool
     */
    public function hasVerifiedMobile(): bool
    {
        return ! is_null($this->mobile_verified_at);
    }

    /**
     * Mark the given user's mobile as verified.
     *
     * @return bool
     */
    public function markMobileAsVerified(): bool
    {
        return $this->forceFill([
            'mobile_verify_code' => NULL,
            'mobile_verified_at' => $this->freshTimestamp(),
            'mobile_attempts_left' => 0,
        ])->save();
    }

    /**
     * Send the mobile verification notification.
     *
     * @return void
     */
    public function sendMobileVerificationNotification(bool $newData = false): void
    {
        if($newData)
        {
            $this->forceFill([
                'mobile_verify_code' => random_int(111111, 999999), // Must be 6 digits
                'mobile_attempts_left' => config('mobile.max_attempts'),
                'mobile_verify_code_sent_at' => now(),
            ])->save();
        }
        $this->notify(new SendVerifySMS());
    }

    /**
     * Sent OTP code for login user
     */
    public function sendMobileVerificationNotificationForLoginUser(bool $newData = false): void
    {
        $this->forceFill([
            // 'mobile_verify_code' => random_int(111111, 999999), // Must be 6 digits
            'mobile_verify_code' => random_int(111111, 999999), // Must be 6 digits
            'mobile_attempts_left' => config('mobile.max_attempts'),
            'mobile_verify_code_sent_at' => now(),
        ])->save();
        $this->notify(new SendVerifySMS());
    }

    /**
     * Get the mobile number that should be used for verification.
     * Implement MustVerifyMobile interface method same as getEmailForVerification() method in MustVerifyEmail trait
     * @return string
     */
    public function getMobileForVerification()
    {
        return $this->mobile_number;
    }

}
