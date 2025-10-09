<?php

namespace Pace;

use Pace\Soap\Factory as SoapFactory;
use Illuminate\Support\ServiceProvider;

class PaceServiceProvider extends ServiceProvider
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
        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']['pace'];

            return new Client(
                new SoapFactory(),
                $config['host'],
                $config['login'],
                $config['password'],
                $config['scheme']
            );
        });

        $this->app->singleton(RestClient::class, function ($app) {
            $config = $app['config']['pace'];

            return new RestClient(
                new \Pace\Rest\Factory(),
                $config['host'],
                $config['login'],
                $config['password'],
                $config['scheme']
            );
        });

        // Register a factory that returns the appropriate client based on protocol
        $this->app->singleton('pace.client', function ($app) {
            $config = $app['config']['pace'];
            
            if ($config['protocol'] === 'rest') {
                return $app->make(RestClient::class);
            }
            
            return $app->make(Client::class);
        });
    }
}
