<?php

namespace TiGR\Form;

/**
 * Набросанный на скорую руку простейший менеджер форм. Единственная задача
 * которого на данный момент - обеспечить единый механизм передачи и обработки
 * метаданных и данных формы.
 *
 * Местами как бы не совсем красиво написано, но для такой простой реализации сгодится.
 *
 * @package Form
 */

use TiGR\Form\Element\Element;
use TiGR\Form\Element\ElementInterface;

/**
 * Базовый класс формы, который объединяет все элементы вместе и обеспечивает их
 * связь с внешним миром.
 */
class Form
{
    const METHOD_POST = 1;
    const METHOD_GET = 2;
    public $method = self::METHOD_POST;
    /**
     * @type Element[] массив элементов формы
     */
    protected $elements = [];
    /**
     * @var array массив элементов формы, распределённых по группам.
     */
    protected $groups = [];
    protected $activeGroups = [];
    /**
     * Это поле определяет элемент, значение которого будет задавать активную в
     * данный момент группу элементов.
     * @var array Имя элемента формы, контролирующего группы.
     */
    protected $groupControlElements = [];
    protected $errorsOccurred = 0;
    protected $submitted;
    private $includeElementsWithEmptyGroup = false;
    private $request;

    /**
     * Form constructor.
     * @param int $method
     */
    public function __construct($method = self::METHOD_POST)
    {
        $this->setMethod($method);
    }

    /**
     * Добавление элемента в форму
     * @param ElementInterface|Element $element элемент формы
     * @return ElementInterface|Element $element
     */
    public function addElement(ElementInterface $element)
    {
        $this->elements[$element->name] = $element;
        if (empty($element->groups)) {
            $this->groups[$element->name] = array('__ALL__');
        } else {
            $groups = is_array($element->groups)
                ? $element->groups
                : explode(' ', $element->groups);
            $this->groups[$element->name] = $groups;
        }

        return $element;
    }

    /**
     * Выставляет значения элементов формы.
     * @param bool $restrictInput
     * @return bool
     */
    public function proceed($restrictInput = false)
    {
        if (!$this->isSubmitted()) {
            return false;
        }

        $this->errorsOccurred = 0;
        $this->populateValues();

        foreach ($this->getActiveElements() as $element) {
            if (!$element->validate()) {
                $this->errorsOccurred++;
            }
        }

        if ($restrictInput and array_diff(
                array_keys($this->getRequestArray()), array_keys($this->getActiveElements())
            )
        ) {
            return false;
        }

        return !$this->errorsOccurred;
    }

    public function isSubmitted()
    {
        $this->populateValues();

        return $this->submitted;
    }

    public function populateValues($force = false)
    {
        if (null === $this->submitted or $force) {
            foreach ($this->elements as $element) {
                $value = $this->getRequestValue($element->name);
                if (isset($value)) {
                    $this->submitted = true;
                    $element->setValue($value);
                } elseif (!empty($this->getRequestArray())) {
                    $element->reset();
                }
            }

            if (null === $this->submitted) {
                $this->submitted = false;
            }
        }
    }

    protected function getRequestValue($name, $default = null)
    {
        $request = $this->getRequestArray();

        return isset($request[$name]) ? $request[$name] : $default;
    }

    protected function &getRequestArray()
    {
        static $methods;

        if ($this->request !== null) {
            return $this->request;
        }

        if (!isset($methods)) {
            $methods = [
                self::METHOD_GET => &$_GET,
                self::METHOD_POST => &$_POST,
            ];
        }

        return $methods[$this->method];
    }

    public function setRequestArray(array $request)
    {
        $this->request = $request;
    }

    /**
     * Возращает массив активных в данный момент элементов.
     *
     * @return Element[] массив элементов активной группы
     */
    public function getActiveElements()
    {
        $activeGroups = $this->getActiveGroups();

        if (!$activeGroups) {
            return $this->elements;
        }

        $elements = [];

        foreach ($this->groups as $elementName => $groups) {
            if (array_intersect($activeGroups, $groups) or
                ($this->includeElementsWithEmptyGroup and in_array('__ALL__', $groups))
            ) {
                $element = $this->getElement($elementName);
                $elements[$elementName] = $element;
            }
        }

        return $elements;
    }

    /**
     * Задаёт элемент, определяющий активную группу.
     *
     * @deprecated use addGroupControlElement instead
     * @param string $name имя контролирующего элемента
     */
    public function setGroupControlElement($name)
    {
        $this->groupControlElements = [$name];
    }

    public function addGroupControlElement($name)
    {
        $this->groupControlElements[] = $name;
    }

    /**
     * Возвращает имена элементов управляющих группами
     * @return array Массив имён управляющих группами элементов
     */
    public function getGroupControlElements()
    {
        return $this->groupControlElements;
    }

    /**
     * @return array|bool
     */
    public function getActiveGroups()
    {
        if (!$this->activeGroups and $this->groupControlElements) {
            $this->activeGroups = [];

            foreach ($this->groupControlElements as $name) {
                $value = $this->getElement($name)->getRealValue();

                if ($value !== null) {
                    $this->activeGroups[] = $value;
                }
            }
        }

        return $this->activeGroups ?: false;
    }

    /**
     * @deprecated use getActiveGroups instead
     * @return array|bool|mixed
     */
    public function getActiveGroup()
    {
        $this->getActiveGroups();

        return $this->activeGroups ? reset($this->activeGroups) : false;
    }

    /**
     * @deprecated use setActiveGroups instead
     * @param $group
     */
    public function setActiveGroup($group)
    {
        $this->activeGroups = [$group];
    }

    /**
     * Возвращает элемент.
     *
     * @param string $name название элемента
     * @return Element Элемент формы
     */
    public function getElement($name)
    {
        if (!$this->hasElement($name)) {
            throw new \InvalidArgumentException('Bad element name provided: ' . $name);
        }

        return $this->elements[$name];
    }

    public function hasElement($name)
    {
        return isset($this->elements[$name]);
    }

    public function getErrors()
    {
        $errors = [];

        if (!$this->isSubmitted()) {
            return $errors;
        }

        if ($this->errorsOccurred) {
            foreach ($this->elements as $element) {
                foreach ($element->getErrors() as $error) {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    public function includeElementsWithEmptyGroup($flag)
    {
        $this->includeElementsWithEmptyGroup = $flag;
    }

    public function exportState()
    {
        $state = [
            'activeGroups' => $this->activeGroups,
            'errorsOccurred' => $this->errorsOccurred,
        ];
        foreach ($this->elements as $name => $element) {
            $state['elements'][$name] = $element->exportState();
        }

        return $state;
    }

    public function importState(array $state)
    {
        $this->errorsOccurred = $state['errorsOccurred'];
        if (isset($state['activeGroups']) and $state['activeGroups']) {
            $this->activeGroups = array_intersect(array_keys($this->groups), $state['activeGroups']);
        }
        foreach ($this->elements as $name => $element) {
            if (isset($state['elements'][$name])) {
                $element->importState($state['elements'][$name]);
            }
        }
    }

    /**
     * @return int
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param int $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getValues($activeOnly = false, $realValues = false)
    {
        $values = [];
        $elements = $activeOnly ? $this->getActiveElements() : $this->getElements();
        foreach ($elements as $element) {
            $values[$element->name] = ($realValues ? $element->getRealValue() : $element->getValue());
        }

        return $values;
    }

    public function getValue($elementName, $real = false)
    {
        $element = $this->getElement($elementName);

        return $real ? $element->getRealValue() : $element->getValue();
    }

    public function getRealValue($elementName)
    {
        return $this->getValue($elementName, true);
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function removeElement($name)
    {
        unset($this->elements[$name]);
    }
}
