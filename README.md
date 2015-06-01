BootstrapForm, forms for Laravel 5
==================================

[![Total Downloads](https://poser.pugx.org/watson/bootstrap-form/downloads.svg)](https://packagist.org/packages/watson/bootstrap-form)
[![Latest Stable Version](https://poser.pugx.org/watson/bootstrap-form/v/stable.svg)](https://packagist.org/packages/watson/bootstrap-form)
[![Latest Unstable Version](https://poser.pugx.org/watson/bootstrap-form/v/unstable.svg)](https://packagist.org/packages/watson/bootstrap-form)
[![License](https://poser.pugx.org/watson/bootstrap-form/license.svg)](https://packagist.org/packages/watson/bootstrap-form)


This is a package for simply creating Bootstrap 3 styled form groups in Laravel 5. It extends the normal form builder to provide you with horizontal form groups completed with labels, error messages and appropriate class usage.

## Introduction

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

## Installation

Simply pop this in your `composer.json` file and run `composer update` (however your Composer is installed).

    "watson/bootstrap-form": "dev-master"

Now, add these service providers to your `app/config/app.php` file.

    'Collective\Html\HtmlServiceProvider',
    'Watson\BootstrapForm\BootstrapFormServiceProvider',

And finally add these to the aliases array (note: Form and Html must be listed before BootstrapForm):

    'Form'=> 'Collective\Html\FormFacade',
    'HTML'=> 'Collective\Html\HtmlFacade',
    'BootstrapForm' => 'Watson\BootstrapForm\Facades\BootstrapForm',

Feel free to use a different alias for BootstrapForm if you'd prefer something shorter.

## Configuration

There are a number of configuration options available for BootstrapForm. Run the following Artisan command to publish the configuration option to your `config` directory:

    php artisan vendor:publish

### Horizontal form sizes

When using a horizontal form you can specify here the default sizes of the left and right columns. Note you can specify as many classes as you like for each column for improved mobile responsiveness, for example:

    col-md-3 col-sm-6 col-xs-12

### Display errors

By default this package will only display the first validation error for each field. If you'd instead like to list out all the validation errors for a field, simply set this configuration option to true.

## Usage

### Opening a form

BoostrapForm has improved the process of opening forms, both in terms of providing Bootstrap classes as well as managing models for model-based forms.

    // Passing an existing, persisted model will trigger a model
    // binded form.
    $user = User::whereEmail('example@example.com')->first();

    // Named routes
    BootstrapForm::open(['model' => $user, 'store' => 'users.store', 'update' => 'users.update']);

    // COntroller actions
    BootstrapForm::open(['model' => $user, 'store' => 'UsersController@store', 'update' => 'UsersController@update']);

If a model is passed to the open method, it will be configured to use the `update` route with the `PUT` method. Otherwise it will point to the `store` method as a `POST` request. This way you can use the same opening tag for a form that handles creating and saving.

    // Passing a model that hasn't been saved or a null value as the
    // model value will trigger a `store` form.
    $user = new User;

    BoostrapForm::open()

### Form variations

There are a few helpers for opening the different kinds of Bootstrap forms. By default, `open()` will use the the form style that you have set in the configuration file. These helpers take the same input as the `open()` method.

    // Open a vertical Bootstrap form.
    BootstrapForm::openVertical();

    // Open an inline Bootstrap form.
    BootstrapForm::openInline();

    // Open a horizontal Bootstrap form.
    BootstrapForm::openHorizontal();

If you want to change the columns for a form for a deviation from the settings in your configuration file, you can also set them through the `$options` array.

    BootstrapForm::open(['left_column_class' => 'col-md-2', 'left_column_offset_clsas' => 'col-md-offset-2', 'right_column_class' => 'col-md-10'])

### Text inputs

Here are the various methods for text inputs. Note that the method signatures are relatively close to those provided by the Laravel form builder but take a parameter for the form label.

    // The label will be inferred as 'Username'.
    BootstrapForm::text('username');

    // The field name by default is 'email'.
    BootstrapForm::email();

    BootstrapForm::textarea('profile');

    // The field name by default is 'password'.
    BootstrapForm::password();

### Checkbox and radio button inputs

Checkboxes and radio buttons are a little bit different and generate different markup. They support both the horizontal and inline layout of the inputs.

View the method signature for configuration options.

    // A checked checkbox.
    BootstrapForm::checkbox('interests', 'Laravel', 'laravel', true);

    // An unchecked, but inline checkbox.
    BootstrapForm::checkbox('interests', 'Rails', 'rails', null, true);

Same goes for radio inputs.

    BootstrapForm::radio('gender', 'Male', 'male');

#### Multiple checkboxes and radio buttons

By simply passing an array of value/label pairs you can generate a group of checkboxes or radio buttons easily.

    $label = 'this is just a label';

    $interests = [
        'laravel' => 'Laravel',
        'rails'   => 'Rails',
        'ie6'     => 'Internet Explorer 6'
    ];

    // Checkbox inputs with Laravel and Rails selected.
    BootstrapForm::checkboxes('interests', $label, $interests, ['laravel', 'rails']);

    $genders = [
        'male'   => 'Male',
        'female' => 'Female'
    ];

    // Gender inputs inline, 'Gender' label inferred.
    BootstrapForm::radios('gender', null, $genders, null, true);

    // Gender inputs with female selected.
    BootstrapForm::radios('gender', 'Gender', $genders, 'female');

### Submit button

    // Pretty simple.
    BootstrapForm::submit('Login');

### Closing the form

    // Pretty simple.
    BootstrapForm::close();
