<?php

namespace App\Listeners;

// use Laravel\Passport\Events\AccessTokenCreated;
use App\Models\AccessToken;
use App\Interface\MustVerifyMobile;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
// use Laravel\Passport\Events\AccessTokenCreated;
class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(AccessToken $event): void
    {
        /// set value to $event
        if($event instanceof MustVerifyMobile && (!$event->hasVerifiedMobile())){
            $event->sendMobileVerificationNotificationForLoginUser(true);
        }
    }
}
