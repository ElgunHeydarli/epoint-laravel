<?php

namespace AZPayments\Epoint;

use Illuminate\Support\ServiceProvider;

class EpointServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/epoint.php', 'epoint'
        );

        $this->app->singleton('epoint', function ($app) {
            return new Epoint();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/epoint.php' => config_path('epoint.php'),
        ], 'epoint-config');

        $this->loadRoutesFrom(__DIR__ . '/routes/epoint.php');
    }
}