<?php

namespace Pace;

use Pace\Rest\Factory as RestFactory;
use Illuminate\Support\ServiceProvider;

class PaceRestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/pace.php' => config_path('pace.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RestClient::class, function ($app) {
            $config = $app['config']['pace'];

            return new RestClient(
                new RestFactory(),
                $config['host'],
                $config['login'],
                $config['password'],
                $config['scheme']
            );
        });

        // Register alias for backward compatibility
        $this->app->alias(RestClient::class, 'pace.rest');
    }
}
