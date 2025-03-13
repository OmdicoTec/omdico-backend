<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Channels\SmsChannel;
use App\Message\SmsMessage;


/**
 * If we were to send many notifications in one go, we wouldn't want our page or command to freeze up until they were all sent.
 * So implement the ShouldQueue interface and use the Queueable trait.
 */
class SendVerifySMS extends Notification implements ShouldQueue
{
    use Queueable;

    public $fromUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function via($notifiable): array
    {
        return [SmsChannel::class];
    }
    /**
     * Send SMS via custom channel
     * (create our notification channel)
     *
     * @param mixed $notifiable
     */
    public function toSms($notifiable)
    {
        // TODO: Language support
        // We are assuming we are notifying a user or a model that has a mobile_number attribute/field.
        return (new SmsMessage)
                    ->from('Bearish')
                    ->to($notifiable->userMobileNumber())
                    // ->line("Your verification code is: ")
                    ->line($notifiable->mobile_verify_code);
                    // ->line("xzadfzxgabxcbg");
    }

    /**
     * To array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }

}
