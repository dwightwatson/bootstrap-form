<?php namespace Watson\BootstrapForm\Facades;

use Illuminate\Support\Facades\Facade;

class BootstrapForm extends Facade
{
	protected static function getFacadeAccessor() { return 'bootstrap-form'; }
}