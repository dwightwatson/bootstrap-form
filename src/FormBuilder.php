<?php
/**
 * laravel
 *
 * @author    Jérémy GAULIN <jeremy@bnb.re>
 * @copyright 2017 - B&B Web Expertise
 */

namespace Bnb\BootstrapForm;

use Illuminate\Support\Collection;

class FormBuilder extends \Collective\Html\FormBuilder
{
    public function __construct(\Collective\Html\FormBuilder $form) {
        parent::__construct($form->html, $form->url, $form->view, $form->csrfToken, $form->request);
    }


    /**
     * Create a placeholder select element option.
     *
     * @param $display
     * @param $selected
     *
     * @return \Illuminate\Support\HtmlString
     */
    protected function placeholderOption($display, $selected)
    {
        $selected = $this->getSelectedValue(null, $selected);

        $options = [
            'selected' => $selected,
            'value' => ''
        ];

        return $this->toHtmlString('<option' . $this->html->attributes($options) . '>' . e($display) . '</option>');
    }

    /**
     * Determine if the value is selected.
     *
     * @param  string $value
     * @param  string $selected
     *
     * @return null|string
     */
    protected function getSelectedValue($value, $selected)
    {
        if (is_array($selected)) {
            return in_array($value, $selected, true) || in_array((string) $value, $selected, true) ? 'selected' : null;
        } elseif ($selected instanceof Collection) {
            return $selected->contains($value) ? 'selected' : null;
        }

        return ((string) $value == (string) $selected) ? 'selected' : null;
    }
}