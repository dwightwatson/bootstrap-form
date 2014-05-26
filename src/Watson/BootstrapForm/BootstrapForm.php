<?php namespace Watson\BootstrapForm;

use Illuminate\Config\Repository as Config;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Html\FormBuilder;
use Illuminate\Session\SessionManager as Session;
use Illuminate\Support\Str;

class BootstrapForm
{
    /**
     * Illuminate HtmlBuilder instance.
     *
     * @var \Illuminate\Html\FHtmlBuilder
     */
    protected $html;

    /**
     * Illuminate FormBuilder instance.
     *
     * @var \Illuminate\Html\FormBuilder
     */
	protected $form;

    /**
     * Illuminate Repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Illuminate SessionManager instance.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

	public function __construct(HtmlBuilder $html, FormBuilder $form, Config $config, Session $session)
	{
        $this->html = $html;
		$this->form = $form;
        $this->config = $config;
        $this->session = $session;
	}

    /**
     * Create a Bootstrap text field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function text($name, $label = null, $value = null, $options = [])
    {
        return $this->input('text', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap email field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function email($name, $label = null, $value = null, $options = [])
    {
        return $this->input('email', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap textarea field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function textarea($name, $label = null, $value = null, $options = [])
    {
        return $this->input('textarea', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap password field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array  $options
     * @return string
     */
    public function password($name, $label = null, $options = [])
    {
        return $this->input('password', $name, $label, null, $options);
    }

    /**
     * Create a Bootstrap label.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function label($name, $value = null, $options = [])
    {
        $options = $this->getLabelOptions($options);

        return $this->form->label($name, $value, $options);
    }

    /**
     * Create the input group for an element with the correct classes for errors.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    protected function input($type, $name, $label = null, $value = null, $options = [])
    {
        $label = $label ?: Str::title($name);

        $options = $this->getFieldOptions($options);
        $wrapperOptions = ['class' => $this->getRightColumnClass()];

        $element = '<div '.$this->html->attributes($wrapperOptions).'>'.$this->form->{$type}($name, $value, $options).$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $element);
    }

    /**
     * Get a form group comprised of a label, form element and errors.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  string  $element
     * @return string
     */
    protected function getFormGroup($name, $value, $element)
    {
        $options = $this->getFormGroupOptions($name);

        return '<div '.$this->html->attributes($options).'>'.$this->label($name, $value).$element.'</div>';
    }

    /**
     * Merge the options provided for a form group with the default options
     * required for Bootstrap styling.
     *
     * @param  string $name
     * @param  array  $options
     * @return array
     */
    protected function getFormGroupOptions($name, $options = [])
    {
        $class = trim('form-group ' . $this->getFieldErrorClass($name));

        return array_merge(['class' => $class], $options);
    }

    /**
     * Merge the options provided for a field with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getFieldOptions($options = [])
    {
        return array_merge(['class' => 'form-control'], $options);
    }

    /**
     * Merge the options provided for a label with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getLabelOptions($options = [])
    {
        $class = trim('control-label ' . $this->getLeftColumnClass());

        return array_merge(['class' => $class], $options);
    }

    /**
     * Get the column class for the left class of a horizontal form.
     *
     * @return string
     */
    protected function getLeftColumnClass()
    {
        return $this->config->get('bootstrap-form::left_column');
    }

    /**
     * Get the column class for the right class of a horizontal form.
     *
     * @return string
     */
    protected function getRightColumnClass()
    {
        return $this->config->get('bootstrap-form::right_column');
    }

    /**
     * Get the MessageBag of errors that is populated by the
     * validator.
     *
     * @return \Illuminate\Support\MessageBag
     */
    protected function getErrors()
    {
        return $this->session->get('errors');
    }

    /**
     * Get the first error for a given field, using the provided
     * format, defaulting to the normal Bootstrap 3 format.
     *
     * @param  string  $field
     * @param  string  $format
     * @return string
     */
    protected function getFieldError($field, $format = '<span class="help-block">:message</span>')
    {
        if ( ! $this->getErrors()) return;

        return $this->getErrors()->first($field, $format);
    }

    /**
     * Return the error class if the given field has associated
     * errors, defaulting to the normal Bootstrap 3 error class.
     *
     * @param  string  $field
     * @param  string  $class
     * @return string
     */
    protected function getFieldErrorClass($field, $class = 'has-error')
    {
        return $this->getFieldError($field) ? $class : null;
    }
}