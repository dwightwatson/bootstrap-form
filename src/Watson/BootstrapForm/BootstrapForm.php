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
        if (isset($options['model']) && $options['model']->getKey())
        {
            $options['route'] = array($options['update'], $options['model']->getKey());
            $options['method'] = 'put';
        }
        // Otherwise, we're storing a brand new model using the POST method.
        else if (isset($options['store']))
        {
            $options['route'] = $options['store'];
            $options['method'] = 'post';
        }

        // Forget the routes provided to the input.
        array_forget($options, 'update');
        array_forget($options, 'create');

        // Set the HTML5 role.
        $options['role'] = 'form';

        // If the class hasn't been set, set the default style.
        if ( ! isset($options['class']))
        {
            $options['class'] = $this->getDefaultFormClass();
        }

        return $this->form->open($options);
    }

    public function openStandard(array $options = array())
    {
        $options = array_merge(array('class' => ''), $options);

        return $this->open($options);
    }

    /**
     * Open an inline Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function openInline(array $options = array())
    {
        $options = array_merge(array('class' => 'form-inline'), $options);

        return $this->open($options);
    }

    /**
     * Open a horizontal Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function openHorizontal(array $options = array())
    {
        $options = array_merge(array('class' => 'form-horizontal'), $options);

        return $this->open($options);
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
     * Create a Bootstrap checkbox input.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  string   $value
     * @param  boolean  $checked
     * @param  boolean  $inline
     * @param  boolean  $wrapper
     * @param  array    $options
     * @return string
     */
    public function checkbox($name, $label, $value, $checked = null, $inline = false, $options = array())
    {
        $labelOptions = $inline ? array('class' => 'checkbox-inline') : array();

        $inputElement = $this->form->checkbox($name, $value, $checked, $options);
        $labelElement = '<label '.$this->html->attributes($labelOptions).'>'.$inputElement.$label.'</label>';

        return $inline ? $labelElement : '<div class="checkbox">'.$labelElement.'</div>';
    }

    /**
     * Create a collection of Bootstrap checkboxes.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  array    $choices
     * @param  array    $values
     * @param  array    $checkedValues
     * @param  boolean  $inline
     * @param  array    $options
     */
    public function checkboxes($name, $label = null, $choices = array(), $checkedValues = array(), $inline = false, $options = array())
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel)
        {
            $checked = in_array($value, (array) $checkedValues);

            $elements .= $this->checkbox($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        return $this->getFormGroup($name, $label, $elements);
    }

    /**
     * Create a Bootstrap radio input.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  string   $value
     * @param  boolean  $checked
     * @param  boolean  $inline
     * @param  boolean  $wrapper
     * @param  array    $options
     * @return string
     */
    public function radio($name, $label, $value, $checked = null, $inline = false, $options = array())
    {
        $labelOptions = $inline ? array('class' => 'radio-inline') : array();

        $inputElement = $this->form->radio($name, $value, $checked, $options);
        $labelElement = '<label '.$this->html->attributes($labelOptions).'>'.$inputElement.$label.'</label>';

        return $inline ? $labelElement : '<div class="radio">'.$labelElement.'</div>';
    }

    /**
     * Create a collection of Bootstrap radio inputs.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  array    $choices
     * @param  array    $values
     * @param  string   $checkedValue
     * @param  boolean  $inline
     * @param  array    $options
     * @return string
     */
    public function radios($name, $label = null, $choices = array(), $checkedValue = null, $inline = false, $options = array())
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel)
        {
            $checked = $value === $checkedValue;

            $elements .= $this->radio($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        return $this->getFormGroup($name, $label, $elements);
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
        $label = $this->getLabelTitle($label, $name);

        $options = $this->getFieldOptions($options);
        $wrapperOptions = array('class' => $this->getRightColumnClass());

        $inputElement = $type == 'password' ? $this->form->password($name, $options) : $this->form->{$type}($name, $value, $options);

        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
    }

    /**
     * Get the label title for a form field, first by using the provided one
     * or titleizing the field name.
     *
     * @param  string  $label
     * @param  string  $name
     * @return string
     */
    protected function getLabelTitle($label, $name)
    {
        return $label ?: Str::title($name);
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
     * Get the default form style.
     *
     * @return string
     */
    protected function getDefaultFormClass()
    {
        return $this->config->get('bootstrap-form::default_class');
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

        $allErrors = $this->config->get('bootstrap-form::all_errors');

        if ($allErrors)
        {
            return $this->getErrors()->get($field, $format);
        }

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