<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/**
 * Поле даты. Т.е., <input type="date" />
 */
class Date extends Text
{

    public $min = null;
    public $max = null;

    protected $type = "date";

    /**
     * @param string $name название поля.
     * @param string $title человекопонятное название поля.
     * @param bool $required флаг, является ли это поле обязательным.
     * @param string $groups группы, к которым относится этот элемент, разделённые пробелом.
     * @param null $min
     * @param null $max
     */
    public function __construct($name, $title, $required = false, $groups = "", $min = null, $max = null)
    {
        parent::__construct($name, $title, $required, $groups, 0);
        $this->min = $min;
        $this->max = $max;
    }
}
