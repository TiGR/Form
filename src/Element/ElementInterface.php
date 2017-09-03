<?php

/**
 * @package Form
 */

namespace TiGR\Form\Element;

/**
 * Интерфейс элементов формы
 */
interface ElementInterface
{
    public function validate();

    public function getType();

    public function setValue($value);

    public function getValue();

    public function getRealValue();

    public function addValidator($name, callable $callback, $errorMessage = null);

    public function addErrorMessage($name, $message);

    public function getErrors();

    public function exportState();

    public function importState(array $state);
}
