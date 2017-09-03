<?php

namespace TiGR\Form\Element;

/**
 * @method static Textarea create($name, $title, $required = false, $groups = "", $help = "", $rows = 4, $cols = 80)
 */
class Textarea extends Element
{
    protected $type = "textarea";
    private $rows;
    private $cols;

    public function __construct($name, $title, $required = false, $groups = "", $help = "", $rows = 4, $cols = 80)
    {
        parent::__construct($name, $title, $required, $groups, $help);
        $this->rows = $rows;
        $this->cols = $cols;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function setRows($rows)
    {
        $this->rows = (int)$rows;

        return $this;
    }

    public function getCols()
    {
        return $this->cols;
    }

    public function setCols($cols)
    {
        $this->cols = (int)$cols;

        return $this;
    }

}
