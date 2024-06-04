<?php

namespace App\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Vanilo\Payment\PaymentGateways;
use Vanilo\Properties\PropertyTypes;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Application services
        $this->app->bind(\App\Contracts\UserManager::class, function ($app) {
            return new \App\Services\UserManager();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @param Kernel $kernel
     * @return void
     */
    public function boot(Kernel $kernel)
    {
        // Custom models and enums
        $this->app->concord->registerModel(
            \Konekt\User\Contracts\User::class,
            \App\Models\User::class
        );
        $this->app->concord->registerEnum(
            \Konekt\User\Contracts\UserType::class,
            \App\Models\UserType::class
        );

        // Relations morph map
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
        ]);
    }
}
