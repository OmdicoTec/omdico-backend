<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Listeners\SendMobileVerificationNotification;

// for login user
use App\Models\AccessToken;
use App\Listeners\LogSuccessfulLogin;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            // SendEmailVerificationNotification::class,
            SendMobileVerificationNotification::class, // send OTP sms to user for mobile verification after registration
            // php artisan make:listener SendMobileVerificationNotification --event=Illuminate\\Auth\\Events\\Registered
        ],
        'eloquent.created: ' . AccessToken::class => [
            LogSuccessfulLogin::class,
        ],
    ];

    /**
     * The subscribers to register.
     *
     * @var array
     */
    protected $subscribe = [
        // \App\Listeners\UserSubscriber::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
        // Event::listen('eloquent.*', function ($eventName, $params){
        //     $event = array_shift($params);
        //     var_dump($eventName);
        // });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
