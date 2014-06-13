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
     * Open a form while passing a model and the routes for storing or updating 
     * the model. This will set the correct route along with the correct 
     * method.
     *
     * @param  array  $options
     * @return string
     */
    public function open(array $options = array())
    {
        // If the form is passed a model, we'll use the update route to update 
        // the model using the PUT method.
        if ($options['model'])
        {
            $options['route'] = array($options['update'], $model->getKey());
            $options['method'] = 'put';
        }
        // Otherwise, we're storing a brand new model using the POST method.
        else
        {
            $options['route'] = $options['store'];
            $options['method'] = 'post';
        }

        // Forget the routes provided to the input.
        array_forget($options, 'update');
        array_forget($options, 'create');

        return $this->form->open($options);
    }

    /**
     * Create a Bootstrap text field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function text($name, $label = null, $value = null, $options = array())
    {
        return $this->input('text', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap email field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function email($name = 'email', $label = null, $value = null, $options = array())
    {
        return $this->input('email', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap textarea field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function textarea($name, $label = null, $value = null, $options = array())
    {
        return $this->input('textarea', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap password field input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $options
     * @return string
     */
    public function password($name = 'password', $label = null, $options = array())
    {
        return $this->input('password', $name, $label, null, $options);
    }

    /**
     * Create a Bootstrap label.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function label($name, $value = null, $options = array())
    {
        $options = $this->getLabelOptions($options);

        return $this->form->label($name, $value, $options);
    }

    /**
     * Create a Boostrap submit button.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function submit($value = null, $options = array())
    {
        $options = array_merge(array('class' => 'btn btn-primary'), $options);

        return $this->form->submit($value, $options);
    }

    /**
     * Create the input group for an element with the correct classes for errors.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function input($type, $name, $label = null, $value = null, $options = array())
    {
        $label = $label ?: Str::title($name);

        $options = $this->getFieldOptions($options);
        $wrapperOptions = array('class' => $this->getRightColumnClass());

        $inputElement = $type == 'password' ? $this->form->password($name, $options) : $this->form->{$type}($name, $value, $options);

        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
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
    protected function getFormGroupOptions($name, $options = array())
    {
        $class = trim('form-group ' . $this->getFieldErrorClass($name));

        return array_merge(array('class' => $class), $options);
    }

    /**
     * Merge the options provided for a field with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getFieldOptions($options = array())
    {
        return array_merge(array('class' => 'form-control'), $options);
    }

    /**
     * Merge the options provided for a label with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getLabelOptions($options = array())
    {
        $class = trim('control-label ' . $this->getLeftColumnClass());

        return array_merge(array('class' => $class), $options);
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