<?php
namespace Watson\BootstrapForm;

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
     * @var \Illuminate\Html\HtmlBuilder
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


    /**
     * @param HtmlBuilder   $html
     * @param FormBuilder   $form
     * @param Config        $config
     * @param Session       $session
     */
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
        // Set the HTML5 role.
        $options['role'] = 'form';

        // If the class hasn't been set, set the default style.
        if ( ! isset($options['class'])) {
            $defaultForm = $this->getDefaultForm();

            if ($defaultForm === 'horizontal') {
                $options['class'] = 'form-horizontal';
            } elseif ($defaultForm === 'inline') {
                $options['class'] = 'form-inline';
            }
        }

        if (isset($options['model'])) {
            return $this->model($options);
        }

        return $this->form->open($options);
    }

    /**
     * Close the form
     *
     * @return mixed
     */
    public function close()
    {
        return $this->form->close();
    }

    /**
     * Open a form configured for model binding.
     *
     * @param  array  $options
     * @return string
     */
    protected function model($options)
    {
        $model = $options['model'];

        // If the form is passed a model, we'll use the update route to update
        // the model using the PUT method.
        if ($options['model']->exists) {
            $options['route'] = [$options['update'], $options['model']->getKey()];
            $options['method'] = 'PUT';
        } else {
            // Otherwise, we're storing a brand new model using the POST method.
            $options['route'] = $options['store'];
            $options['method'] = 'POST';
        }

        // Forget the routes provided to the input.
        array_forget($options, ['model', 'update', 'store']);

        return $this->form->model($model, $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public function openStandard(array $options = array())
    {
        $options = array_merge(['class' => null], $options);

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
        $options = array_merge(['class' => 'form-inline'], $options);

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
        $options = array_merge(['class' => 'form-horizontal'], $options);

        return $this->open($options);
    }

    /**
     * Create a bootstrap static field
     *
     * @param $name
     * @param null $label
     * @param null $value
     * @param array $options
     * @return string
     */
    public function staticField($name, $label = null, $value = null, $options = array())
    {
        $options = array_merge(['class' => 'form-control-static'], $options);

        $label = $this->getLabelTitle($label, $name);
        $inputElement = '<p'.$this->html->attributes($options).'>'.e($value).'</p>';

        $wrapperOptions = ['class' => $this->getRightColumnClass()];
        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
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
     * @param  array    $options
     * @return string
     */
    public function checkbox($name, $label, $value, $checked = null, $inline = false, $options = array())
    {
        $labelOptions = $inline ? ['class' => 'checkbox-inline'] : [];

        $inputElement = $this->form->checkbox($name, $value, $checked, $options);
        $labelElement = '<label '.$this->html->attributes($labelOptions).'>'.$inputElement.$label.'</label>';

        return $inline ? $labelElement : '<div class="checkbox">'.$labelElement.'</div>';
    }

    /**
     * Create a collection of Bootstrap checkboxes.
     *
     * @param  string $name
     * @param  string $label
     * @param  array $choices
     * @param  array $checkedValues
     * @param  boolean $inline
     * @param  array $options
     * @return string
     */
    public function checkboxes($name, $label = null, $choices = array(), $checkedValues = array(), $inline = false, $options = array())
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel) {
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
     * @param  array    $options
     * @return string
     */
    public function radio($name, $label, $value, $checked = null, $inline = false, $options = array())
    {
        $labelOptions = $inline ? ['class' => 'radio-inline'] : [];

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
     * @param  string   $checkedValue
     * @param  boolean  $inline
     * @param  array    $options
     * @return string
     */
    public function radios($name, $label = null, $choices = array(), $checkedValue = null, $inline = false, $options = array())
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel) {
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
        $options = array_merge(['class' => 'btn btn-primary'], $options);

        return $this->form->submit($value, $options);
    }

    /**
     * Create a Boostrap file upload button.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $options
     * @return string
     */
    public function file($name, $label = null, $options = array())
    {
        $label = $this->getLabelTitle($label, $name);

        $options = array_merge(['class' => 'filestyle', 'data-buttonBefore' => 'true'], $options);

        $options = $this->getFieldOptions($options);

        $wrapperOptions = ['class' => $this->getRightColumnClass()];

        $inputElement = $this->form->input('file', $name, null, $options);

        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
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
        $wrapperOptions = ['class' => $this->getRightColumnClass()];

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

        return array_merge(['class' => $class], $options);
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
        $options['class'] = trim('form-control ' . $this->getFieldOptionsClass($options));

        return $options;
    }

    /**
     * Returns the class property from the options, or the empty string
     *
     * @param   $options
     * @return  string
     */
    protected function getFieldOptionsClass($options)
    {
        return array_get($options, 'class');
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

        return array_merge(['class' => $class], $options);
    }

    /**
     * Get the default form style.
     *
     * @return string
     */
    protected function getDefaultForm()
    {
        return $this->config->get('bootstrap-form.default_form');
    }

    /**
     * Get the column class for the left class of a horizontal form.
     *
     * @return string
     */
    protected function getLeftColumnClass()
    {
        return $this->config->get('bootstrap-form.left_column');
    }

    /**
     * Get the column class for the right class of a horizontal form.
     *
     * @return string
     */
    protected function getRightColumnClass()
    {
        return $this->config->get('bootstrap-form.right_column');
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
     * @return mixed
     */
    protected function getFieldError($field, $format = '<span class="help-block">:message</span>')
    {
        if ($this->getErrors()) {
            $allErrors = $this->config->get('bootstrap-form.all_errors');

            if ($allErrors) {
                return $this->getErrors()->get($field, $format);
            }

            return $this->getErrors()->first($field, $format);
        }
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
