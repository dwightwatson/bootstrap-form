<?php
namespace Watson\BootstrapForm;

use Illuminate\Support\ServiceProvider;

class BootstrapFormServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'bootstrap-form');

        $this->app->bind('bootstrap-form', function ($app) {
            return new BootstrapForm(
                $app['html'],
                $app['form'],
                $app['config'],
                $app['session']
            );
        });
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('bootstrap-form.php')
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['bootstrap-form'];
    }
}
