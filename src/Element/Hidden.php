<?php

namespace TiGR\Form\Element;

/**
 * Class Hidden
 * @package TiGR\Form\Element
 * @method static Text create($name, $required = false, $groups = "")
 */
class Hidden extends Element
{
    protected $type = 'hidden';

    public function __construct($name, $required = false, $groups = "")
    {
        return parent::__construct($name, '', $required, $groups);
    }
}
