<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\Carbon;
// Update the default token model to use our custom token model Passport
use App\Models\AccessToken;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * Since version 11 passport's routes have been moved to a dedicated route file.
     * You can remove the Passport::routes() call from your application's service provider.
     * If you dont want to use default passport routes.
     * you can disabled the route in register method inside AppServicerProvider
     *
     * public function register()
     * {
     *   Passport::ignoreRoutes();
     * }
     *
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Update the default token model to use our custom token model Passport
        Passport::useTokenModel(AccessToken::class); // Hint: working
        // Passport::routes();
        Passport::personalAccessTokensExpireIn(Carbon::now()->addHours(744));
    }
}
