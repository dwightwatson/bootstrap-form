<?php
/**
 * laravel
 *
 * @author    Jérémy GAULIN <jeremy@bnb.re>
 * @copyright 2017 - B&B Web Expertise
 */

namespace Bnb\BootstrapForm;

class FormBuilder extends \Collective\Html\FormBuilder
{

    /**
     * Default values that prevail over models one (lower priority than old and request)
     *
     * @var array
     */
    protected $defaultValues = [];


    /**
     * FormBuilder constructor.
     *
     * @param \Collective\Html\FormBuilder $form
     */
    public function __construct($form)
    {
        parent::__construct($form->html, $form->url, $form->view, $form->csrfToken, $form->request);
    }


    public function getValueAttribute($name, $value = null)
    {
        if ($value === null && isset($this->defaultValues['name'])) {
            $value = $this->defaultValues['name'];
        }

        return parent::getValueAttribute($name, $value);
    }


    /**
     * Set the default values that prevail over model ones (useful for filter)
     *
     * @param array $values
     *
     * @return void
     */
    public function setDefaultValues(array $values)
    {
        $this->defaultValues = $values;
    }
}