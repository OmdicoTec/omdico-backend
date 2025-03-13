<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification): void
    {
        // Remember that we created the toSms() methods in our notification class
        // class SendVerifySMS extends Notification implements ShouldQueue
        $message = $notification->toSms($notifiable);

        $message->send();

       // Or use dryRun() for testing to send it, without sending it for real.
        // $message->dryRun();
    }
}
