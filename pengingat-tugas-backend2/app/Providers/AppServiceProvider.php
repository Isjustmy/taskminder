<?php

namespace App\Providers;

use App\Auth\CustomPasswordBroker;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('auth.password', function ($service, $app) {
            return new PasswordBrokerManager($app, function ($app) {
                return new CustomPasswordBroker(
                    $app->make('auth.password.tokens'),
                    $app['mailer'],
                    $app['auth']->createUserProvider($config['provider'])
                );
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
