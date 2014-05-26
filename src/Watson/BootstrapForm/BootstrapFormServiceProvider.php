<?php namespace Watson\BootstrapForm;

use Illuminate\Support\ServiceProvider;

class BootstrapFormServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('bootstrap-form', function($app)
		{
			return new \Watson\BootstrapForm\BootstrapForm(
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
		$this->package('watson/bootstrap-form');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('bootstrap-form');
	}

}