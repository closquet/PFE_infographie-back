<?php

namespace aleafoodapi\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'aleafoodapi\Model' => 'aleafoodapi\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes(function ($router) {
            $router->forAccessTokens();
//            $router->forPersonalAccessTokens();
//            $router->forTransientTokens();
        });

        Passport::tokensExpireIn(now()->addMinutes(1000000));

        Passport::refreshTokensExpireIn(now()->addDays(10));

        Passport::personalAccessTokensExpireIn(now()->addMinutes(10));
        //
    }
}
