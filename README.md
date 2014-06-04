Bootstrap forms for Laravel 4
=============================

This is a package for simply creating Bootstrap 3 styled form groups in Laravel 4. It extends the normal form builder to provide you with horizontal form groups completed with labels, error messages and appropriate class usage.

## Installation

Simply pop this in your `composer.json` file and run `composer update` (however your Composer is installed).

	"watson/bootstrap-form": "0.8.*"

_I won't hit version 1.0 until I have completed writing tests._

Now, add the service provided to your `app/config/app.php` file.

	'Watsons\BootstrapForm\BootstrapFormServiceProvider'

And finally add this to the aliases array.

	'BootstrapForm' => 'Watson\BootstrapForm\Facades\BootstrapForm

## Using Bootstrap forms

Simply use the `BootstrapForm` facade in the place of the `Form` facade when you want to generate a Bootstrap 3 form group.

	BootstrapForm::text('username');

And you'll get back the following:

	<div class="form-group">
		<label for="username" class="control-label col-md-2">Username</label>
		<div class="col-md-10">
			<input type="text" name="username" class="form-control">
		</div>
	</div>

Of course, if there are errors for that field it will even populate them.

	<div class="form-group has-error">
		<label for="username" class="control-label col-md-2">Username</label>
		<div class="col-md-10">
			<input type="text" name="username" class="form-control">
			<span class="help-block">The username field is required.</span>
		</div>
	</div>