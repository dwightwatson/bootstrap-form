<?php namespace Watson\BootstrapForm;

use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Session\SessionManager as Session;
use Illuminate\Support\Str;

class BootstrapForm
{
    /**
     * Illuminate HtmlBuilder instance.
     *
     * @var \Collective\Html\HtmlBuilder
     */
    protected $html;

    /**
     * Illuminate FormBuilder instance.
     *
     * @var \Collective\Html\FormBuilder
     */
    protected $form;

    /**
     * Illuminate Repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Bootstrap form type class.
     *
     * @var string
     */
    protected $type;

    /**
     * Bootstrap form left column class.
     *
     * @var string
     */
    protected $leftColumnClass;

    /**
     * Bootstrap form left column offset class.
     *
     * @var string
     */
    protected $leftColumnOffsetClass;

    /**
     * Bootstrap form right column class.
     *
     * @var string
     */
    protected $rightColumnClass;

    /**
     * Construct the class.
     *
     * @param  \Collective\Html\HtmlBuilder             $html
     * @param  \Collective\Html\FormBuilder             $form
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @return void
     */
    public function __construct(HtmlBuilder $html, FormBuilder $form, Config $config)
    {
        $this->html = $html;
        $this->form = $form;
        $this->config = $config;
    }

    /**
     * Open a form while passing a model and the routes for storing or updating
     * the model. This will set the correct route along with the correct
     * method.
     *
     * @param  array  $options
     * @return string
     */
    public function open(array $options = [])
    {
        // Set the HTML5 role.
        $options['role'] = 'form';

        // Set the class for the form type.
        if (!array_key_exists('class', $options)) {
            $options['class'] = $this->getType();
        }

        if (array_key_exists('left_column_class', $options)) {
            $this->setLeftColumnClass($options['left_column_class']);
        }

        if (array_key_exists('left_column_offset_class', $options)) {
            $this->setLeftColumnOffsetClass($options['left_column_offset_class']);
        }

        if (array_key_exists('right_column_class', $options)) {
            $this->setRightColumnClass($options['right_column_class']);
        }

        array_forget($options, [
            'left_column_class', 
            'left_column_offset_class', 
            'right_column_class'
        ]);

        if (array_key_exists('model', $options)) {
            return $this->model($options);
        }

        return $this->form->open($options);
    }

    /**
     * Reset and close the form.
     *
     * @return string
     */
    public function close()
    {
        $this->type = null;

        $this->leftColumnClass = $this->rightColumnClass = null;

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
        if (!is_null($options['model']) && $options['model']->exists) {
            $route = Str::contains($options['update'], '@') ? 'action' : 'route';

            $options[$route] = [$options['update'], $options['model']->getKey()];
            $options['method'] = 'PUT';
        } else {
            // Otherwise, we're storing a brand new model using the POST method.
            $route = Str::contains($options['store'], '@') ? 'action' : 'route';

            $options[$route] = $options['store'];
            $options['method'] = 'POST';
        }

        // Forget the routes provided to the input.
        array_forget($options, ['model', 'update', 'store']);

        return $this->form->model($model, $options);
    }

    /**
     * Open a vertical (standard) Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function vertical(array $options = [])
    {
        $this->setType(Type::VERTICAL);

        return $this->open($options);    }

    /**
     * Open an inline Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function inline(array $options = [])
    {
        $this->setType(Type::INLINE);

        return $this->open($options);
    }

    /**
     * Open a horizontal Bootstrap form.
     *
     * @param  array  $options
     * @return string
     */
    public function horizontal(array $options = [])
    {
        $this->setType(Type::HORIZONTAL);

        return $this->open($options);
    }

    /**
     * Create a Bootstrap static field.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function staticField($name, $label = null, $value = null, array $options = [])
    {
        $options = array_merge(['class' => 'form-control-static'], $options);

        $label = $this->getLabelTitle($label, $name);
        $inputElement = '<p' . $this->html->attributes($options) . '>' . e($value) . '</p>';

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : []; 
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
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
    public function text($name, $label = null, $value = null, array $options = [])
    {
        return $this->input('text', $name, $label, $value, $options);
    }

    /**
     * Create a text field with a prepended glyphicon
     * @param string $name
     * @param string $icon
     * @param string $label
     * @param string $value
     * @param array $options
     * @return string
     */
    public function textWithPrepend($name, $icon, $label = null, $value = null, array $options = []) {
        $label = $this->getLabelTitle($label, $name);

        $options = $this->getFieldOptions($options);
        $inputElement = $this->form->text($name, $value, $options);

        $inputGroup = '<div class="input-group"><span class="input-group-addon">'.
            '<span class="glyphicon glyphicon-' . $icon .
            '" aria-hidden="true"></span></span>'. $inputElement .'</div>';

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputGroup . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
    }

    /**
     * Create a text field with an appended glyphicon
     * @param string $name
     * @param string $icon
     * @param string $label
     * @param string $value
     * @param array $options
     * @return string
     */
    public function textWithAppend($name, $icon, $label = null, $value = null, array $options = []) {
        $label = $this->getLabelTitle($label, $name);

        $options = $this->getFieldOptions($options);
        $inputElement = $this->form->text($name, $value, $options);

        $inputGroup = '<div class="input-group">'. $inputElement .'<span class="input-group-addon">'.
            '<span class="glyphicon glyphicon-' . $icon .
            '" aria-hidden="true"></span></span></div>';

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputGroup . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
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
    public function email($name = 'email', $label = null, $value = null, array $options = [])
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
    public function textarea($name, $label = null, $value = null, array $options = [])
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
    public function password($name = 'password', $label = null, array $options = [])
    {
        return $this->input('password', $name, $label, null, $options);
    }

    /**
     * Create a Bootstrap checkbox input.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  string   $value
     * @param  bool     $checked
     * @param  array    $options
     * @return string
     */
    public function checkbox($name, $label = null, $value = 1, $checked = null, array $options = [])
    {
        $inputElement = $this->checkboxElement($name, $label, $value, $checked, false, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . '</div>';

        return $this->getFormGroup(null, $wrapperElement);
    }

    /**
     * Create a single Bootstrap checkbox element.
     *
     * @param  string   $name
     * @param  string   $label
     * @param  string   $value
     * @param  bool     $checked
     * @param  bool     $inline
     * @param  array    $options
     * @return string
     */
    public function checkboxElement($name, $label = null, $value = 1, $checked = null, $inline = false, array $options = [])
    {
        $label = $this->getLabelTitle($label, $name);

        $labelOptions = $inline ? ['class' => 'checkbox-inline'] : [];

        $inputElement = $this->form->checkbox($name, $value, $checked, $options);
        $labelElement = '<label ' . $this->html->attributes($labelOptions) . '>' . $inputElement . $label . '</label>';

        return $inline ? $labelElement : '<div class="checkbox">' . $labelElement . '</div>';
    }

    /**
     * Create a collection of Bootstrap checkboxes.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $choices
     * @param  array   $checkedValues
     * @param  bool    $inline
     * @param  array   $options
     * @return string
     */
    public function checkboxes($name, $label = null, $choices = [], $checkedValues = [], $inline = false, array $options = [])
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel) {
            $checked = in_array($value, (array) $checkedValues);

            $elements .= $this->checkboxElement($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $elements . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
    }

    /**
     * Create a Bootstrap radio input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  bool    $checked
     * @param  array   $options
     * @return string
     */
    public function radio($name, $label = null, $value = null, $checked = null, array $options = [])
    {
        $inputElement = $this->radioElement($name, $label, $value, $checked, false, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . '</div>';

        return $this->getFormGroup(null, $wrapperElement);
    }

    /**
     * Create a single Bootstrap radio input.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $value
     * @param  bool    $checked
     * @param  bool    $inline   
     * @param  array   $options
     * @return string
     */
    public function radioElement($name, $label = null, $value = null, $checked = null, $inline = false, array $options = [])
    {
        $label = $this->getLabelTitle($label, $name);
        $Value = $value ?: $label;

        $labelOptions = $inline ? ['class' => 'radio-inline'] : [];

        $inputElement = $this->form->radio($name, $value, $checked, $options);
        $labelElement = '<label ' . $this->html->attributes($labelOptions) . '>' . $inputElement . $label . '</label>';

        return $inline ? $labelElement : '<div class="radio">' . $labelElement . '</div>';
    }

    /**
     * Create a collection of Bootstrap radio inputs.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $choices
     * @param  string  $checkedValue
     * @param  bool    $inline
     * @param  array   $options
     * @return string
     */
    public function radios($name, $label = null, $choices = [], $checkedValue = null, $inline = false, array $options = [])
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel) {
            $checked = $value === $checkedValue;

            $elements .= $this->radioElement($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $elements . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
    }

    /**
     * Create a Bootstrap label.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function label($name, $value = null, array $options = [])
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
    public function submit($value = null, array $options = [])
    {
        $options = array_merge(['class' => 'btn btn-primary'], $options);

        $inputElement = $this->form->submit($value, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>'. $inputElement . '</div>';

        return $this->getFormGroup(null, $wrapperElement);
    }

    /**
     * Create a Boostrap file upload button.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $options
     * @return string
     */
    public function file($name, $label = null, array $options = [])
    {
        $label = $this->getLabelTitle($label, $name);

        $options = array_merge(['class' => 'filestyle', 'data-buttonBefore' => 'true'], $options);

        $options = $this->getFieldOptions($options);
        $inputElement = $this->form->input('file', $name, null, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
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
    public function input($type, $name, $label = null, $value = null, array $options = [])
    {
        $label = $this->getLabelTitle($label, $name);

        $options = $this->getFieldOptions($options);
        $inputElement = $type === 'password' ? $this->form->password($name, $options) : $this->form->{$type}($name, $value, $options);
        
        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
    }

    /**
     * Create a select box field.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  array   $list
     * @param  string  $selected
     * @param  array   $options
     * @return string
     */
    public function select($name, $label = null, $list = [], $selected = null, array $options = [])
    {
        $label = $this->getLabelTitle($label, $name);

        $options = $this->getFieldOptions($options);
        $inputElement = $this->form->select($name, $list, $selected, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div ' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . '</div>';

        return $this->getFormGroupWithLabel($name, $label, $wrapperElement);
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
    protected function getFormGroupWithLabel($name, $value, $element)
    {
        $options = $this->getFormGroupOptions($name);

        return '<div ' . $this->html->attributes($options) . '>' . $this->label($name, $value) . $element . '</div>';
    }

    /**
     * Get a form group.
     *
     * @param  string  $name
     * @param  string  $element
     * @return string
     */
    public function getFormGroup($name = null, $element)
    {
        $options = $this->getFormGroupOptions($name);

        return '<div ' . $this->html->attributes($options) . '>' . $element . '</div>';
    }

    /**
     * Merge the options provided for a form group with the default options
     * required for Bootstrap styling.
     *
     * @param  string $name
     * @param  array  $options
     * @return array
     */
    protected function getFormGroupOptions($name = null, array $options = [])
    {
        $class = 'form-group';

        if ($name) {
            $class .= ' ' . $this->getFieldErrorClass($name);
        }

        return array_merge(['class' => $class], $options);
    }

    /**
     * Merge the options provided for a field with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getFieldOptions(array $options = [])
    {
        $options['class'] = trim('form-control ' . $this->getFieldOptionsClass($options));

        return $options;
    }

    /**
     * Returns the class property from the options, or the empty string
     *
     * @param   array  $options
     * @return  string
     */
    protected function getFieldOptionsClass(array $options = [])
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
    protected function getLabelOptions(array $options = [])
    {
        $class = 'control-label';
        if ($this->isHorizontal()) {
            $class .= ' ' . $this->getLeftColumnClass();
        }

        return array_merge(['class' => trim($class)], $options);
    }

    /** 
     * Get the form type.
     *
     * @return string
     */
    public function getType()
    {
        return isset($this->type) ? $this->type : $this->config->get('bootstrap_form.type');
    }

    /**
     * Determine if the form is of a horizontal type.
     *
     * @return bool
     */
    public function isHorizontal()
    {
        return $this->getType() === Type::HORIZONTAL;
    }

    /** 
     * Set the form type.
     *
     * @param  string  $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the column class for the left column of a horizontal form.
     *
     * @return string
     */
    public function getLeftColumnClass()
    {
        return $this->leftColumnClass ?: $this->config->get('bootstrap_form.left_column_class');
    }

    /**
     * Set the column class for the left column of a horizontal form.
     *
     * @param  string  $class
     * @return void
     */
    public function setLeftColumnClass($class)
    {
        $this->leftColumnClass = $class;
    }

    /**
     * Get the column class for the left column offset of a horizontal form.
     *
     * @return string
     */
    public function getLeftColumnOffsetClass()
    {
        return $this->leftColumnOffsetClass ?: $this->config->get('bootstrap_form.left_column_offset_class');
    }

    /**
     * Set the column class for the left column offset of a horizontal form.
     *
     * @param  string  $class
     * @return void
     */
    public function setLeftColumnOffsetClass($class)
    {
        $this->leftColumnOffsetClass = $class;
    }

    /**
     * Get the column class for the right column of a horizontal form.
     *
     * @return string
     */
    public function getRightColumnClass()
    {
        return $this->rightColumnClass ?: $this->config->get('bootstrap_form.right_column_class');
    }

    /**
     * Set the column class for the right column of a horizontal form.
     *
     * @param  string  $lcass
     * @return void
     */
    public function setRightColumnClass($class)
    {
        $this->rightColumnClass = $class;
    }

    /**
     * Get the MessageBag of errors that is populated by the
     * validator.
     *
     * @return \Illuminate\Support\MessageBag
     */
    protected function getErrors()
    {
        return $this->form->getSessionStore()->get('errors');
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
            $allErrors = $this->config->get('bootstrap_form.show_all_errors');

            if ($allErrors) {
                return implode('', $this->getErrors()->get($field, $format));
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
