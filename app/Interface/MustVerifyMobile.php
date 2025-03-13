<?php

namespace App\Interface;

interface MustVerifyMobile
{
    /**
     * Has the user verified their mobile number?
     *
     */
    public function hasVerifiedMobile();

    /**
     * Mark the given user's mobile as verified.
     *
     */
    public function markMobileAsVerified();

    /**
     * Send the mobile verification notification.
     *
    */
    public function sendMobileVerificationNotification();

    /**
     * Sent OTP code for login user
     */
    public function sendMobileVerificationNotificationForLoginUser();
    /**
     * Get the mobile number that should be used for verification.
     *
     */
    public function getMobileForVerification();

}
