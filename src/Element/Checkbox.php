<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/**
 * Группа checkbox элементов. <input type="checkbox" />
 */
class Checkbox extends Dropdown
{
    protected $type = "checkbox";
    protected $value = [];

    public function __construct($name, $title, $options, $required = false, $groups = "", $help = "")
    {
        parent::__construct($name, $title, $options, false, $groups, $help);
        $this->required = $required;
        if ($this->required) {
            $this->addValidator(
                'required', function ($value) {
                return !empty($value);
            },
                'Пожалуйста, выберите хотя бы одно значение поля «%s»'
            );
        }
    }

    public function getValue()
    {
        if (is_null($this->value)) {
            return null;
        }
        $result = array();
        foreach ($this->value as $val) {
            $result[$val] = $this->options[$val];
        }

        return $result;
    }

    public function setValue($value)
    {
        $this->reset();

        foreach ((array)$value as $val) {
            if (isset($this->options[$val])) {
                $this->value[$val] = $val;
            }
        }

        return $this;
    }

    public function reset()
    {
        $this->value = [];
    }
}
