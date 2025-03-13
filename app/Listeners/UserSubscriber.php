<?php

namespace App\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

class UserSubscriber
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen("eloquent.booting: App\Models\User", [$this,"register"]);
    }

    public function register()
    {
        // Log::info("user");
        // var_dump("Milaaaaaaaaaaaaaaaaaad");
    }
}
