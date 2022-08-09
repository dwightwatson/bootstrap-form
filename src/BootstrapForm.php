<?php

namespace Watson\BootstrapForm;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Config\Repository as Config;

class BootstrapForm
{
    use Macroable;

    /**
     * Bootstrap form type class.
     */
    protected ?string $type = null;

    /**
     * Bootstrap form left column class.
     */
    protected ?string $leftColumnClass = null;

    /**
     * Bootstrap form left column offset class.
     */
    protected ?string $leftColumnOffsetClass = null;

    /**
     * Bootstrap form right column class.
     */
    protected ?string $rightColumnClass = null;

    /**
     * The icon prefix.
     */
    protected string $iconPrefix;

    /**
     * The errorbag that is used for validation (multiple forms).
     */
    protected ?string $errorBag = null;

    /**
     * The error class.
     */
    protected ?string $errorClass = null;


    /**
     * Construct the class.
     *
     * @return void
     */
    public function __construct(
        protected HtmlBuilder $html,
        protected FormBuilder $form,
        protected Config $config
    ) {
    }

    /**
     * Open a form while passing a model and the routes for storing or updating
     * the model. This will set the correct route along with the correct
     * method.
     */
    public function open(array $options = []): string
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

        Arr::forget($options, [
            'left_column_class',
            'left_column_offset_class',
            'right_column_class'
        ]);

        if (array_key_exists('model', $options)) {
            return $this->model($options);
        }

        if (array_key_exists('error_bag', $options)) {
            $this->setErrorBag($options['error_bag']);
        }

        return $this->form->open($options);
    }

    /**
     * Reset and close the form.
     */
    public function close(): string
    {
        $this->type = null;

        $this->leftColumnClass = $this->rightColumnClass = null;

        return $this->form->close();
    }

    /**
     * Open a form configured for model binding.
     */
    protected function model(array $options): string
    {
        $model = $options['model'];

        if (isset($options['url'])) {
            // If we're explicity passed a URL, we'll use that.
            Arr::forget($options, ['model', 'update', 'store']);
            $options['method'] = isset($options['method']) ? $options['method'] : 'GET';

            return $this->form->model($model, $options);
        }

        // If we're not provided store/update actions then let the form submit to itself.
        if (!isset($options['store']) && !isset($options['update'])) {
            Arr::forget($options, 'model');
            return $this->form->model($model, $options);
        }

        if (!is_null($options['model']) && $options['model']->exists) {
            // If the form is passed a model, we'll use the update route to update
            // the model using the PUT method.
            $name = is_array($options['update']) ? Arr::first($options['update']) : $options['update'];
            $route = Str::contains($name, '@') ? 'action' : 'route';

            $options[$route] = array_merge((array) $options['update'], [$options['model']->getRouteKey()]);
            $options['method'] = 'PUT';
        } else {
            // Otherwise, we're storing a brand new model using the POST method.
            $name = is_array($options['store']) ? Arr::first($options['store']) : $options['store'];
            $route = Str::contains($name, '@') ? 'action' : 'route';

            $options[$route] = $options['store'];
            $options['method'] = 'POST';
        }

        // Forget the routes provided to the input.
        Arr::forget($options, ['model', 'update', 'store']);

        return $this->form->model($model, $options);
    }

    /**
     * Open a vertical (standard) Bootstrap form.
     */
    public function vertical(array $options = []): string
    {
        $this->setType(Type::VERTICAL);

        return $this->open($options);
    }

    /**
     * Open an inline Bootstrap form.
     */
    public function inline(array $options = []): string
    {
        $this->setType(Type::INLINE);

        return $this->open($options);
    }

    /**
     * Open a horizontal Bootstrap form.
     */
    public function horizontal(array $options = []): string
    {
        $this->setType(Type::HORIZONTAL);

        return $this->open($options);
    }

    /**
     * Create a Bootstrap static field.
     */
    public function staticField(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        $options = array_merge(['class' => 'form-control-static'], $options);

        if (is_array($value) and isset($value['html'])) {
            $value = $value['html'];
        } else {
            $value = e($value);
        }

        $label = $this->getLabelTitle($label, $name);
        $inputElement = '<p' . $this->html->attributes($options) . '>' . $value . '</p>';

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . $this->getHelpText($name, $options) . '</div>';

        return $this->getFormGroup($name, $label, $wrapperElement);
    }

    /**
     * Create a Bootstrap text field input.
     */
    public function text(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('text', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap email field input.
     */
    public function email(string $name = 'email', null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('email', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap URL field input.
     */
    public function url(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('url', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap tel field input.
     */
    public function tel(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('tel', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap number field input.
     */
    public function number(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('number', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap date field input.
     */
    public function date(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('date', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap time field input.
     */
    public function time(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('time', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap textarea field input.
     */
    public function textarea(string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        return $this->input('textarea', $name, $label, $value, $options);
    }

    /**
     * Create a Bootstrap password field input.
     */
    public function password(string $name = 'password', null|string|HtmlString $label = null, array $options = []): string
    {
        return $this->input('password', $name, $label, null, $options);
    }

    /**
     * Create a Bootstrap checkbox input.
     */
    public function checkbox(string $name, null|string|HtmlString $label = null, string $value = '1', ?bool $checked = null, array $options = []): string
    {
        $inputElement = $this->checkboxElement($name, $label, $value, $checked, false, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . $this->getHelpText($name, $options) . '</div>';

        return $this->getFormGroup($name, null, $wrapperElement);
    }

    /**
     * Create a single Bootstrap checkbox element.
     */
    public function checkboxElement(string $name, null|string|HtmlString $label = null, string $value = '1', ?bool $checked = null, bool $inline = false, array $options = []): string
    {
        $label = $label === false ? null : $this->getLabelTitle($label, $name);

        $labelOptions = $inline ? ['class' => 'checkbox-inline'] : [];
        $inputElement = $this->form->checkbox($name, $value, $checked, $options);
        $labelElement = '<label ' . $this->html->attributes($labelOptions) . '>' . $inputElement . $label . '</label>';

        return $inline ? $labelElement : '<div class="checkbox">' . $labelElement . '</div>';
    }

    /**
     * Create a collection of Bootstrap checkboxes.
     */
    public function checkboxes(string $name, null|string|HtmlString $label = null, array $choices = [], array $checkedValues = [], bool $inline = false, array $options = []): string
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel) {
            $checked = in_array($value, (array) $checkedValues);

            $elements .= $this->checkboxElement($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $elements . $this->getFieldError($name) . $this->getHelpText($name, $options) . '</div>';

        return $this->getFormGroup($name, $label, $wrapperElement);
    }

    /**
     * Create a Bootstrap radio input.
     */
    public function radio(string $name, null|string|HtmlString $label = null, ?string $value = null, ?bool $checked = null, array $options = []): string
    {
        $inputElement = $this->radioElement($name, $label, $value, $checked, false, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . '</div>';

        return $this->getFormGroup(null, $label, $wrapperElement);
    }

    /**
     * Create a single Bootstrap radio input.
     */
    public function radioElement(string $name, null|string|HtmlString $label = null, ?string $value = null, ?bool $checked = null, bool $inline = false, array $options = []): string
    {
        $label = $label === false ? null : $this->getLabelTitle($label, $name);

        $value = is_null($value) ? $label : $value;

        $labelOptions = $inline ? ['class' => 'radio-inline'] : [];

        $inputElement = $this->form->radio($name, $value, $checked, $options);
        $labelElement = '<label ' . $this->html->attributes($labelOptions) . '>' . $inputElement . $label . '</label>';

        return $inline ? $labelElement : '<div class="radio">' . $labelElement . '</div>';
    }

    /**
     * Create a collection of Bootstrap radio inputs.
     */
    public function radios(string $name, null|string|HtmlString $label = null, array  $choices = [], ?string $checkedValue = null, bool $inline = false, array $options = []): string
    {
        $elements = '';

        foreach ($choices as $value => $choiceLabel) {
            $checked = $value === $checkedValue;

            $elements .= $this->radioElement($name, $choiceLabel, $value, $checked, $inline, $options);
        }

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $elements . $this->getFieldError($name) . $this->getHelpText($name, $options) . '</div>';

        return $this->getFormGroup($name, $label, $wrapperElement);
    }

    /**
     * Create a Bootstrap label.
     */
    public function label(string $name, null|HtmlString|string $value = null, array $options = []): string
    {
        $options = $this->getLabelOptions($options);

        $escapeHtml = false;

        if (is_array($value) and isset($value['html'])) {
            $value = $value['html'];
        } elseif ($value instanceof HtmlString) {
            $value = $value->toHtml();
        } else {
            $escapeHtml = true;
        }

        return $this->form->label($name, $value, $options, $escapeHtml);
    }

    /**
     * Create a Boostrap submit button.
     */
    public function submit(?string $value = null, array $options = []): string
    {
        $options = array_merge(['class' => 'btn btn-primary'], $options);

        $inputElement = $this->form->submit($value, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . '</div>';

        return $this->getFormGroup(null, null, $wrapperElement);
    }

    /**
     * Create a Boostrap button.
     */
    public function button(?string $value = null, array $options = []): string
    {
        $options = array_merge(['class' => 'btn btn-primary'], $options);

        $inputElement = $this->form->button($value, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => implode(' ', [$this->getLeftColumnOffsetClass(), $this->getRightColumnClass()])] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . '</div>';

        return $this->getFormGroup(null, null, $wrapperElement);
    }

    /**
     * Create a Boostrap file upload button.
     */
    public function file(string $name, null|string|HtmlString $label = null, array $options = []): string
    {
        $label = $this->getLabelTitle($label, $name);

        $options = array_merge(['class' => 'filestyle', 'data-buttonBefore' => 'true'], $options);

        $options = $this->getFieldOptions($options, $name);
        $inputElement = $this->form->input('file', $name, null, $options);

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . $this->getHelpText($name, $options) . '</div>';

        return $this->getFormGroup($name, $label, $wrapperElement);
    }

    /**
     * Create the input group for an element with the correct classes for errors.
     */
    public function input(string $type, string $name, null|string|HtmlString $label = null, ?string $value = null, array $options = []): string
    {
        $label = $this->getLabelTitle($label, $name);

        $optionsField = $this->getFieldOptions(Arr::except($options, ['suffix', 'prefix']), $name);

        $inputElement = '';

        if (isset($options['prefix'])) {
            $inputElement = $options['prefix'];
        }

        $inputElement .= $type === 'password' ? $this->form->password($name, $optionsField) : $this->form->{$type}($name, $value, $optionsField);

        if (isset($options['suffix'])) {
            $inputElement .= $options['suffix'];
        }

        if (isset($options['prefix']) || isset($options['suffix'])) {
            $inputElement = '<div class="input-group">' . $inputElement . '</div>';
        }

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . $this->getHelpText($name, $optionsField) . '</div>';

        return $this->getFormGroup($name, $label, $wrapperElement);
    }

    /**
     * Create an addon button element.
     */
    public function addonButton(string $label, array $options = []): string
    {
        $attributes = array_merge(['class' => 'btn', 'type' => 'button'], $options);

        if (isset($options['class'])) {
            $attributes['class'] .= ' btn';
        }

        return '<div class="input-group-btn"><button ' . $this->html->attributes($attributes) . '>' . $label . '</button></div>';
    }

    /**
     * Create an addon text element.
     */
    public function addonText(string $text, array $options = []): string
    {
        return '<div class="input-group-addon"><span ' . $this->html->attributes($options) . '>' . $text . '</span></div>';
    }

    /**
     * Create an addon icon element.
     */
    public function addonIcon(string $icon, array $options = []): string
    {
        $prefix = Arr::get($options, 'prefix', $this->getIconPrefix());

        return '<div class="input-group-addon"><span ' . $this->html->attributes($options) . '><i class="' . $prefix . $icon . '"></i></span></div>';
    }

    /**
     * Create a hidden field.
     */
    public function hidden(string $name, ?string $value = null, array $options = []): string
    {
        return $this->form->hidden($name, $value, $options);
    }

    /**
     * Create a select box field.
     */
    public function select(string $name, null|string|HtmlString $label = null, array $list = [], ?string $selected = null, array $options = []): string
    {
        $label = $this->getLabelTitle($label, $name);

        $inputElement = isset($options['prefix']) ? $options['prefix'] : '';

        $options = $this->getFieldOptions($options, $name);
        $inputElement .= $this->form->select($name, $list, $selected, $options);

        if (isset($options['suffix'])) {
            $inputElement .= $options['suffix'];
        }

        if (isset($options['prefix']) || isset($options['suffix'])) {
            $inputElement = '<div class="input-group">' . $inputElement . '</div>';
        }

        $wrapperOptions = $this->isHorizontal() ? ['class' => $this->getRightColumnClass()] : [];
        $wrapperElement = '<div' . $this->html->attributes($wrapperOptions) . '>' . $inputElement . $this->getFieldError($name) . $this->getHelpText($name, $options) . '</div>';

        return $this->getFormGroup($name, $label, $wrapperElement);
    }


    /**
     * Wrap the content in Laravel's HTML string class.
     *
     * @param  string  $html
     * @return \Illuminate\Support\HtmlString
     */
    protected function toHtmlString($html)
    {
        return new HtmlString($html);
    }

    /**
     * Get the label title for a form field, first by using the provided one
     * or titleizing the field name.
     *
     * @param  string  $label
     * @param  string  $name
     * @return mixed
     */
    protected function getLabelTitle($label, $name)
    {
        if (!$label) {
            return null;
        }

        if (is_null($label) && Lang::has("forms.{$name}")) {
            return Lang::get("forms.{$name}");
        }

        return $label ?: str_replace('_', ' ', Str::title($name));
    }

    /**
     * Get a form group comprised of a form element and errors.
     */
    protected function getFormGroupWithoutLabel(?string $name, ?string $element): HtmlString
    {
        $options = $this->getFormGroupOptions($name);

        return $this->toHtmlString('<div' . $this->html->attributes($options) . '>' . $element . '</div>');
    }

    /**
     * Get a form group comprised of a label, form element and errors.
     */
    protected function getFormGroupWithLabel(?string $name, null|HtmlString|string $value, ?string $element): HtmlString
    {
        $options = $this->getFormGroupOptions($name);

        return $this->toHtmlString('<div' . $this->html->attributes($options) . '>' . $this->label($name, $value) . $element . '</div>');
    }

    /**
     * Get a form group with or without a label.
     */
    public function getFormGroup(?string $name = null, null|string|HtmlString $label = null, ?string $wrapper = null): string
    {
        if (!$label) {
            return $this->getFormGroupWithoutLabel($name, $wrapper);
        }

        return $this->getFormGroupWithLabel($name, $label, $wrapper);
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
     * @param  string $name
     * @return array
     */
    protected function getFieldOptions(array $options = [], $name = null)
    {
        $options['class'] = trim('form-control ' . $this->getFieldOptionsClass($options));

        // If we've been provided the input name and the ID has not been set in the options,
        // we'll use the name as the ID to hook it up with the label.
        if ($name && ! array_key_exists('id', $options)) {
            $options['id'] = $name;
        }

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
        return Arr::get($options, 'class');
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
     */
    public function getType(): ?string
    {
        return isset($this->type) ? $this->type : $this->config->get('bootstrap_form.type');
    }

    /**
     * Determine if the form is of a horizontal type.
     */
    public function isHorizontal(): bool
    {
        return $this->getType() === Type::HORIZONTAL;
    }

    /**
     * Set the form type.
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get the column class for the left column of a horizontal form.
     */
    public function getLeftColumnClass(): ?string
    {
        return $this->leftColumnClass ?: $this->config->get('bootstrap_form.left_column_class');
    }

    /**
     * Set the column class for the left column of a horizontal form.
     */
    public function setLeftColumnClass(string $class): void
    {
        $this->leftColumnClass = $class;
    }

    /**
     * Get the column class for the left column offset of a horizontal form.
     */
    public function getLeftColumnOffsetClass(): ?string
    {
        return $this->leftColumnOffsetClass ?: $this->config->get('bootstrap_form.left_column_offset_class');
    }

    /**
     * Set the column class for the left column offset of a horizontal form.
     */
    public function setLeftColumnOffsetClass(string $class): void
    {
        $this->leftColumnOffsetClass = $class;
    }

    /**
     * Get the column class for the right column of a horizontal form.
     */
    public function getRightColumnClass(): ?string
    {
        return $this->rightColumnClass ?: $this->config->get('bootstrap_form.right_column_class');
    }

    /**
     * Set the column class for the right column of a horizontal form.
     */
    public function setRightColumnClass(string $class): void
    {
        $this->rightColumnClass = $class;
    }

    /**
     * Get the icon prefix.
     */
    public function getIconPrefix(): ?string
    {
        return $this->iconPrefix ?: $this->config->get('bootstrap_form.icon_prefix');
    }

    /**
     * Get the error class.
     */
    public function getErrorClass(): ?string
    {
        return $this->errorClass ?: $this->config->get('bootstrap_form.error_class');
    }

    /**
     * Get the error bag.
     */
    protected function getErrorBag(): ?string
    {
        return $this->errorBag ?: $this->config->get('bootstrap_form.error_bag');
    }

    /**
     * Set the error bag.
     */
    protected function setErrorBag(string $errorBag): void
    {
        $this->errorBag = $errorBag;
    }

    /**
     * Flatten arrayed field names to work with the validator, including removing "[]",
     * and converting nested arrays like "foo[bar][baz]" to "foo.bar.baz".
     */
    public function flattenFieldName(string $field): string
    {
        return preg_replace_callback("/\[(.*)\\]/U", function ($matches) {
            if (!empty($matches[1]) || $matches[1] === '0') {
                return "." . $matches[1];
            }
        }, $field);
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
        $field = $this->flattenFieldName($field);

        if ($this->getErrors()) {
            $allErrors = $this->config->get('bootstrap_form.show_all_errors');

            if ($this->getErrorBag()) {
                $errorBag = $this->getErrors()->{$this->getErrorBag()};
            } else {
                $errorBag = $this->getErrors();
            }

            if ($allErrors) {
                return implode('', $errorBag->get($field, $format));
            }

            return $errorBag->first($field, $format);
        }
    }

    /**
     * Return the error class if the given field has associated
     * errors, defaulting to the normal Bootstrap 3 error class.
     *
     * @param string  $field
     * @param string  $class
     *
     * @return null|string
     */
    protected function getFieldErrorClass($field): ?string
    {
        return $this->getFieldError($field) ? $this->getErrorClass() : null;
    }

    /**
     * Get the help text for the given field.
     *
     * @param string  $field
     * @param array   $options
     *
     * @return HtmlString|string
     */
    protected function getHelpText($field, array $options = []): string|HtmlString
    {
        if (array_key_exists('help_text', $options)) {
            return $this->toHtmlString('<span class="help-block">' . e($options['help_text']) . '</span>');
        }

        return '';
    }
}
