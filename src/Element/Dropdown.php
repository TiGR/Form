<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/**
 * Элемент - выпадающий список, html-элемент select.
 *
 * @method static $this create(string $name, string $title, array $options, bool $required = false, string $groups = "", string $help = "")
 */
class Dropdown extends Element
{

    /**
     * @type array массив опций (элементов). Ключ - внутреннее значение,
     *            значение - отображаемый текст.
     */
    protected $options = array();

    protected $type = "dropdown";

    /**
     * @param string $name название поля.
     * @param string $title человекопонятное название поля.
     * @param array $options массив элементов, где ключ - внутренее представление,
     *                       значение - отображаемое.
     * @param bool $required флаг, является ли это поле обязательным.
     * @param string $groups группы, к которым относится этот элемент, разделённые пробелом.
     * @param string $help справка по элементу.
     */
    public function __construct($name, $title, $options, $required = false, $groups = "", $help = "")
    {
        parent::__construct($name, $title, $required, $groups, $help);
        $this->options = $options;
        if ($required) {
            /** @noinspection PhpUnusedParameterInspection */
            $this->addValidator(
                'required',
                function ($value, ElementInterface $element) {
                    $realValue = $element->getRealValue();

                    return !(is_null($realValue) or $realValue === '');
                }
            );
        }
    }

    public function setValue($value)
    {
        if (isset($this->options[$value])) {
            $this->value = $value;
        }

        return $this;
    }

    public function getValue()
    {
        return isset($this->options[$this->value]) ? $this->options[$this->value] : null;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
