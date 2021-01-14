<?php

namespace Watson\BootstrapForm;

use Illuminate\Support\ServiceProvider;

class BootstrapFormServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'bootstrap_form');

        $this->app->singleton('bootstrap_form', fn ($app) => new BootstrapForm($app['html'], $app['form'], $app['config']));
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('bootstrap_form.php')
        ], 'config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['bootstrap_form'];
    }
}
