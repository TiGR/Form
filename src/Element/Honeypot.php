<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/**
 * Class Honeypot
 * @package polosatus\Form\Element
 * @method static Honeypot create($name)
 */
class Honeypot extends Element
{
    protected $type = 'honeypot';

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($name)
    {
        $this->name = $name;
        $this->addValidator(
            'honeypot',
            function ($value) {
                return empty($value);
            }, 'Ошибка обработки формы'
        );
    }
}
