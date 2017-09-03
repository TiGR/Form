<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/**
 * Текстовое поле. Т.е., <input type="text" />
 *
 * @method static Text create($name, $title, $required = false, $groups = "", $size = 50, $help = "")
 */
class Text extends Element
{
    const TYPE_TEXT = 'text';
    const TYPE_EMAIL = 'email';
    const TYPE_TELEPHONE = 'tel';

    /**
     * @var int размер поля, соответствует html-атрибуту size.
     */
    public $size = 50;
    public $pattern;
    public $autofocus;
    public $maxlength;

    protected $type = self::TYPE_TEXT;

    /**
     * @param string $name название поля.
     * @param string $title человекопонятное название поля.
     * @param bool $required флаг, является ли это поле обязательным.
     * @param string $groups группы, к которым относится этот элемент, разделённые пробелом.
     * @param int $size размер.
     * @param $help
     */
    public function __construct($name, $title, $required = false, $groups = "", $size = 50, $help = "")
    {
        parent::__construct($name, $title, $required, $groups, $help);
        $this->size = $size;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        $this->addValidator('pattern', [$this, 'validatePattern'], 'Значение поля «%s» не соответствует формату.');

        return $this;
    }

    public function validatePattern($value)
    {
        $regex = '~^' . str_replace('~', '\~', $this->pattern) . '$~';

        return preg_match($regex, $value);
    }

    public function setAutofocus($autofocus)
    {
        $this->autofocus = (bool)$autofocus;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxLength()
    {
        return $this->maxlength;
    }

    /**
     * @param mixed $maxlength
     * @return Text
     */
    public function setMaxLength($maxlength)
    {
        $this->maxlength = $maxlength;

        return $this;
    }
}
