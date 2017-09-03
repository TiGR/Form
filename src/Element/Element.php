<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/**
 * Базовый элемент формы
 */
abstract class Element implements ElementInterface
{
    /**
     * @var string внутреннее имя элемента, соответствует html-атрибуту name.
     */
    public $name = "";

    /**
     * @var string человекопонятное название поля.
     */
    public $title = "";

    /**
     * @var bool флаг, определяющий является ли данное поле обязательным.
     */
    public $required = false;

    /**
     * @var string названия групп, в которые входит элемент, разделённые пробелом.
     */
    public $groups = "";

    /**
     * @var string подсказка (помощь) для элемента. Допустим html.
     */
    public $help = "";

    /**
     * @var string
     */
    public $placeholder;

    /**
     * @var string строковое представление типа HTML элемента ввода
     */
    protected $type;

    /**
     * @var bool
     */
    protected $readonly;

    /**
     * @var mixed внутреннее текущее значение элемента.
     */
    protected $value = null;

    /**
     * @var array массив пользовательских валидаторов
     */
    protected $validators = array();

    protected $errors = array();
    protected $errorMessages = array(
        'unknown' => 'Недопустимое значение поля «%s»',
    );

    /**
     * Конструктор, создаёт элемент с указанными параметрами
     *
     * @todo обеспечить единообразие конструкторов для разных типов элементов
     * @param string $name название поля.
     * @param string $title человекопонятное название поля.
     * @param bool $required флаг, является ли это поле обязательным.
     * @param string $groups группы, к которым относится этот элемент, разделённые пробелом.
     * @param string $help справка по элементу.
     */
    public function __construct($name, $title, $required = false, $groups = "", $help = "")
    {
        $this->name = $name;
        $this->title = $title;
        $this->required = $required;
        $this->setGroups($groups);
        $this->help = $help;
        if ($this->required) {
            $this->addValidator(
                'required',
                function ($value) {
                    if (is_null($value) or empty($value)) {
                        return false;
                    }

                    return true;
                }
            );

            if (empty($this->title)) {
                $this->addErrorMessage('required', 'Пожалуйста, заполните это поле');
            } else {
                $this->addErrorMessage('required', 'Пожалуйста, заполните поле «%s»');
            }

        }
    }

    /**
     * Добавляет проверку к полю.
     *
     * @param string $name уникальное название проверки
     * @param callable $callback функция для проверки поля
     * @param string $errorMessage
     * @return Element
     */
    public function addValidator($name, callable $callback, $errorMessage = null)
    {
        $this->validators[$name] = $callback;
        if (isset($errorMessage)) {
            $this->addErrorMessage($name, $errorMessage);
        }

        return $this;
    }

    public function addErrorMessage($name, $message)
    {
        $this->errorMessages[$name] = $message;

        return $this;
    }

    /**
     * Фабрика, создаёт элемент с указанными параметрами
     *
     * @param string $name название поля.
     * @param string $title человекопонятное название поля.
     * @param bool $required флаг, является ли это поле обязательным.
     * @param string $groups группы, к которым относится этот элемент, разделённые пробелом.
     * @param string $help справка по элементу.
     * @return static
     */
    public static function create()
    {
        return new static(...func_get_args());
    }

    /**
     * Выполняет проверку значения элемента и возвращает true в случае успеха.
     *
     * @return bool флаг успешности проверки
     */
    public function validate()
    {
        $this->errors = array();

        foreach ($this->validators as $name => $callback) {
            if (!call_user_func($callback, $this->getValue(), $this)) {
                $this->errors[] = $name;
            }
        }

        return empty($this->errors);
    }

    /**
     * Возвращает значение поля или выбранный элемент в списке. Возвращается
     * человекопонятное значение, а не внутренний код.
     *
     * @return mixed человекопонятное значение элемента.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Задаёт значение элемента.
     *
     * @param mixed $value значение, обычно задаётся из Form.
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string|array $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = (is_array($groups) ? implode(' ', $groups) : $groups);

        return $this;
    }

    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Возвращает строковое название типа элемента, например 'text' или 'radio'.
     *
     * @return string тип элемента
     */
    public function getType()
    {
        return $this->type;
    }

    public function getErrors()
    {
        $errors = array();

        foreach ($this->errors as $type) {
            if (isset($this->errorMessages[$type])) {
                $error = $this->errorMessages[$type];
            } else {
                $error = $this->errorMessages['unknown'];
            }

            $errors[$type] = sprintf($error, $this->title);
        }

        return $errors;
    }

    public function reset()
    {
        $this->value = null;
    }

    public function exportState()
    {
        return [
            'value' => $this->getRealValue(),
            'errors' => $this->errors,
        ];
    }

    /**
     * Возвращает внутреннее значение поля.
     *
     * @return mixed человекопонятное значение элемента
     */
    public function getRealValue()
    {
        return $this->value;
    }

    public function importState(array $state)
    {
        $this->setValue($state['value']);
        $this->errors = $state['errors'];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param string $help
     * @return $this
     */
    public function setHelp($help)
    {
        $this->help = $help;

        return $this;
    }

    /**
     * @return bool
     */
    public function getReadonly()
    {
        return $this->readonly;
    }

    /**
     * @param bool $readonly
     * @return $this
     */
    public function setReadonly($readonly)
    {
        $this->readonly = (bool)$readonly;

        return $this;
    }

}
