<?php

namespace Andrew1601\LaravelArbor;

use Arbor\Model\ModelBase;
use Illuminate\Support\ServiceProvider;
use Arbor\Api\Gateway\RestGateway;

class LaravelArborServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->publishes([
            __DIR__.'/../config/arbor.php' => config_path('arbor.php'),
        ]);

        // Now that the service container has been populated, configure the default gateway for the Arbor SDK.
        // That way we don't need to do it every time, it's ready when the app is booted. Neat!
        ModelBase::setDefaultGateway(resolve(RestGateway::class));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // We only need one RestGateway for the Arbor SDK, so to avoid having to create
        // multiple instances we can register it as a singleton and bind this
        // single instance into the service container for future use.
        $this->app->singleton(RestGateway::class, function ($app) {
            return new RestGateway(config('arbor.url'), config('arbor.username'), config('arbor.password'), config('arbor.user_agent', 'Laravel Arbor'));
        });

        $this->app->singleton(ArborGraphQLClient::class, function () {
            return new ArborGraphQLClient(config('arbor.url') . '/graphql/query', [
                'Authorization' => 'Basic ' . base64_encode(config('arbor.username') . ':' . config('arbor.password')),
            ]);
        });
    }
}